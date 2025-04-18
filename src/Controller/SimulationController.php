<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProductRepository;
use App\Entity\Simulation;
use App\Entity\Enum\BillCategory;
use App\Repository\SimulationRepository;
use App\Repository\ClientRepository;

use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

#[Route('/Simulation')]
class SimulationController extends AbstractController
{
    private LoggerInterface $logger;


    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;


    }

    #[Route('/add', name: 'add_simulation', methods: ['POST'])]
    public function addSimulation(
        Request $request,
        ClientRepository $clientRepo,
        ProductRepository $productRepo,
        EntityManagerInterface $em
    ): Response {
        // Decode the incoming JSON data
        $data = json_decode($request->getContent(), true);
    
        // Validate required data
        if (!$data || !isset($data['client_id'], $data['product_id'], $data['duration_use'], $data['nbr_use'], $data['hour_use'])) {
            return new Response(json_encode(['message' => 'Missing data']), 400, ['Content-Type' => 'application/json']);
        }
    
        // Fetch client and product
        $client = $clientRepo->find($data['client_id']);
        $product = $productRepo->find($data['product_id']);
    
        // Check if client and product exist
        if (!$client || !$product) {
            return new Response(json_encode(['message' => 'Client or Product not found']), 404, ['Content-Type' => 'application/json']);
        }
    
        // Create a new Simulation object
        $simulation = new Simulation();
        $simulation->setProduct($product);
        $simulation->setIdClient($client);
        $simulation->setNbrUse((int)$data['nbr_use']);
        $simulation->setDurationUse((int)$data['duration_use']);
    
        // Convert hour_use into DateTime (assuming it's a valid format)
        $hourUse = \DateTime::createFromFormat('Y-m-d H:i:s', $data['hour_use']);
        if (!$hourUse) {
            return new Response(json_encode(['message' => 'Invalid hour_use format']), 400, ['Content-Type' => 'application/json']);
        }
        $simulation->setHourUse($hourUse);
    
        // Persist the simulation
        $em->persist($simulation);
        $em->flush();
    
        // Prepare response data
        $responseData = [
            'simulation_id' => $simulation->getId(),
            'product_id' => $product->getId(),
            'client_id' => $client->getId(),
            'duration_use' => $simulation->getDurationUse(),
            'nbr_use' => $simulation->getNbrUse(),
            'hour_use' => $simulation->getHourUse()->format('Y-m-d H:i:s')
        ];
    
        // Return success response
        return new Response(json_encode($responseData), 201, ['Content-Type' => 'application/json']);
    }
    
    #[Route('/all/{client_id}', name: 'get_simulations_for_client', methods: ['GET'])]
public function getSimulationsForClient(
    int $client_id, // Directly pass client_id from the URL path
    ClientRepository $clientRepo,
    SimulationRepository $simulationRepo
): Response {
    // Find the client by ID
    $client = $clientRepo->find($client_id);

    if (!$client) {
        return new Response(json_encode(['message' => 'Client not found']), 404, ['Content-Type' => 'application/json']);
    }

    // Get all simulations for this client
    $simulations = $simulationRepo->findBy(['client' => $client]);

    // If no simulations are found, return a message
    if (!$simulations) {
        return new Response(json_encode(['message' => 'No simulations found for this client']), 404, ['Content-Type' => 'application/json']);
    }

    // Prepare the response data
    $simulationData = array_map(function ($simulation) {
        return [
            'id' => $simulation->getId(),
            'product_id' => $simulation->getProduct()->getId(),
            'product_name' => $simulation->getProduct()->getName(),
            'duration_use' => $simulation->getDurationUse(),
            'nbr_use' => $simulation->getNbrUse(),
            'hour_use' => $simulation->getHourUse()->format('Y-m-d H:i:s'),
        ];
    }, $simulations);

    // Prepare the final data
    $data = [
        'client_id' => $client_id,
        'simulations' => $simulationData,
    ];

    // Return the response as JSON
    return new Response(json_encode($data), 200, ['Content-Type' => 'application/json']);
}

#[Route('/simulation/{id}/water-bill', name: 'app_simulation_water_bill', methods: ['GET'])]
public function calculateWaterBill(
    Simulation $simulation,
    Request $request,
    SimulationRepository $simulationRepository
): JsonResponse {
    $periode = $request->query->get('periode', 'mois');
    $billCategory = BillCategory::from(strtolower($periode));

    $amount = $simulationRepository->calculateWaterBill($simulation, $billCategory);

    return $this->json([
        'montant_facture_eau' => round($amount, 2),
        'periode' => $billCategory->value,
    ]);
}

}