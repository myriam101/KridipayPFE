<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProductRepository;
use App\Repository\BonifPointRepository;
use App\Entity\BonifPoint;
use App\Entity\Client;
use App\Entity\Enum\Typepoint;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

use Psr\Log\LoggerInterface;

#[Route('/BonifPoint')]
class BonifPointController extends AbstractController
{
    private LoggerInterface $logger;


    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;

    }

    /** methode d'ajout points au client */
    #[Route('/add', name: 'app_add_bonif_points', methods: ['POST'])]
public function addBonifPointsToClient(
    Request $request,
    ClientRepository $clientRepo,
    ProductRepository $productRepo,
    EntityManagerInterface $em
): Response {
    $clientId = $request->headers->get('client_id');
    $productId = $request->headers->get('product_id');

    if (!$clientId || !$productId) {
        return new Response('Missing client_id or product_id', 400);
    }

    $client = $clientRepo->find($clientId);
    $product = $productRepo->find($productId);

    if (!$client || !$product) {
        return new Response('Client or Product not found', 404);
    }

    $bonifPoint = new BonifPoint();
    $bonifPoint->setIdClient($client);
    $bonifPoint->setNbrPt($product->getBonifpoint());
    $bonifPoint->setRemainingPts($bonifPoint->getNbrPt());
    $bonifPoint->setProduct($product);
    $bonifPoint->setDateWin(new \DateTime());
    $bonifPoint->setTypePoint(Typepoint::ACTIF); 

    $client->setTotalBonifPts($client->getTotalBonifPts() + $bonifPoint->getNbrPt());

    $em->persist($bonifPoint);
    $em->persist($client);  

    $em->flush();

    return new Response("Bonif points added successfully. Total points: " . $client->getTotalBonifPts(), 200);
}

/** displays total number of pts of specified client and the list of points */
#[Route('/details', name: 'get_details', methods: ['GET'])]
public function getClientBonifPoints(
    Request $request,
    ClientRepository $clientRepository,
    BonifPointRepository $bonifPointRepository
): Response {
    $clientId = $request->headers->get('client_id');

    if (!$clientId) {
        return new Response('Missing client_id', 400);
    }

    $client = $clientRepository->find($clientId);

    if (!$client) {
        return new Response('Client not found', 404);
    }

    // Retrieve all bonification points related to the client
    $points = $bonifPointRepository->findBy(['id_client' => $client]);

    // Calculate total points
    $totalPoints = array_sum(array_map(fn($pt) => $pt->getNbrPt(), $points));

    // Prepare the response data including product information for each bonification point
    $data = [
        'client_id' => $clientId,
        'total_points' => $totalPoints,
        'details' => array_map(function ($pt) {
            // Get the associated product for each bonification point
            $product = $pt->getProduct();
            
            // Return details including product information
            return [
                'id' => $pt->getId(),
                'nbr_pt' => $pt->getNbrPt(),
                'date_win' => $pt->getDateWin()->format('Y-m-d H:i:s'),
                'date_use' => $pt->getDateUse()?->format('Y-m-d H:i:s'),
                'remainingPts' => $pt->getRemainingPts(),
                'type' => $pt->getTypePoint()->value,
                'product' => [
                    'id' => $product ? $product->getId() : null,
                    'name' => $product ? $product->getName() : null,
                    'reference' => $product ? $product->getReference() : null,
                    'brand' => $product ? $product->getBrand() : null,
                ]
            ];
        }, $points),
    ];

    return new Response(json_encode($data), 200, ['Content-Type' => 'application/json']);
}


/** gets all bonif points of all clients */
#[Route('/all', name: 'get_all_bonifpoints', methods: ['GET'])]
public function getAllBonifPoints(BonifPointRepository $bonifPointRepository): Response
{
    $bonifPoints = $bonifPointRepository->findAll();

    $data = array_map(function ($pt) {
        return [
            'id' => $pt->getId(),
            'client_id' => $pt->getIdClient()?->getId(),
            'nbr_pt' => $pt->getNbrPt(),
            'date_win' => $pt->getDateWin()?->format('Y-m-d H:i:s'),
            'date_use' => $pt->getDateUse()?->format('Y-m-d H:i:s'),
            'type' => $pt->getTypePoint()->value,
        ];
    }, $bonifPoints);

    return new Response(json_encode($data), 200, ['Content-Type' => 'application/json']);
}

//** logic to use points */
#[Route('/{clientId}/use-points', name: 'use_points', methods: ['POST'])]
public function usePoints(
    int $clientId,
    Request $request,
    BonifPointRepository $bonifPointRepository,
    ClientRepository $clientRepository,
    LoggerInterface $logger
): JsonResponse {
    $logger->info('Received raw content: ' . $request->getContent());

    // Decode JSON body
    $data = json_decode($request->getContent(), true);
    $logger->info('Decoded JSON: ' . json_encode($data));

    // Check if points are provided
    $pointsToUse = $data['points'] ?? null;
    if ($pointsToUse === null || !is_numeric($pointsToUse) || $pointsToUse <= 0) {
        return new JsonResponse(['message' => 'Invalid or missing points'], 400);
    }

    $client = $clientRepository->find($clientId);
    if (!$client) {
        return new JsonResponse(['message' => 'Client not found'], 404);
    }

    try {
        $bonifPointRepository->usePoints($client, (int)$pointsToUse);
        return new JsonResponse(['message' => 'Points used successfully']);
    } catch (\Exception $e) {
        $logger->error('Error using points: ' . $e->getMessage());
        return new JsonResponse(['message' => $e->getMessage()], 400);
    }
}


}