<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\EnergyBill;
use App\Entity\Simulation;
use App\Entity\Feature;
use App\Entity\Enum\BillCategory;
use App\Entity\Enum\TrancheEau;
use App\Entity\PriceWater;
use App\Repository\PriceGazRepository;
use App\Repository\PriceWaterRepository;
use App\Repository\SimulationRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;



#[Route('/PriceWater')]
class PriceWaterController extends AbstractController
{
    private LoggerInterface $logger;


    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;

    }

    
    #[Route('/add', name: 'add_price_water', methods: ['POST'])]
    public function addPriceWater(
        Request $request,
        PriceWaterRepository $repo,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
    
        if (!isset($data['tranche_eau']) || !isset($data['price'])) {
            return new JsonResponse(['error' => 'tranche_eau et price sont requis.'], 400);
        }
    
        $tranche = TrancheEau::from($data['tranche_eau']);
    
        if ($repo->existsByTranche($tranche)) {
            return new JsonResponse(['error' => 'Un tarif avec cette tranche existe déjà.'], 409);
        }
    
        $priceWater = new PriceWater();
        $priceWater->setTrancheEau($tranche);
        $priceWater->setPrice($data['price']);
    
        $this->entityManager->persist($priceWater);
        $this->entityManager->flush();
    
        return new JsonResponse([
            'success' => true,
            'id' => $priceWater->getId(),
            'tranche_eau' => $tranche->value,
            'price' => $priceWater->getPrice(),
        ]);
    }
    
   #[Route('/edit/{id}', name: 'update_price_eau', methods: ['PUT'])]
public function updatePriceEau(
    int $id,
    Request $request,
    PriceWaterRepository $repository
): JsonResponse {
    $priceEau = $repository->find($id);

    if (!$priceEau) {
        return new JsonResponse(['message' => 'Tarif eau introuvable.'], Response::HTTP_NOT_FOUND);
    }

    $data = json_decode($request->getContent(), true);

    // Traitement de l'enum TrancheEau
    try {
        $newTranche = isset($data['tranche_eau'])
            ? TrancheEau::from($data['tranche_eau']) // convertit depuis string
            : $priceEau->getTrancheEau();
    } catch (\ValueError $e) {
        return new JsonResponse(['message' => 'Valeur de tranche_eau invalide.'], Response::HTTP_BAD_REQUEST);
    }

    $existing = $repository->findOneBy(['tranche_eau' => $newTranche]);

    if ($existing && $existing->getId() !== $id) {
        return new JsonResponse([
            'message' => 'Un tarif avec cette tranche d’eau existe déjà.'
        ], Response::HTTP_CONFLICT);
    }

    $priceEau->setTrancheEau($newTranche);

    if (isset($data['price'])) {
        $priceEau->setPrice($data['price']);
    }

    $this->entityManager->flush();

    return new JsonResponse(['message' => 'Tarif eau mis à jour avec succès.']);
}


   
#[Route('/all', name: 'all_water', methods: ['GET'])]
    public function getAllWater(PriceWaterRepository $pr): JsonResponse
    {
        $water = $pr->findAll();

        return $this->json($water, 200, [], ['groups' => 'water:read']);
    }
    
     #[Route('/delete/{id}', name: 'delete_price_eau', methods: ['DELETE'])]
public function deletePriceEau(int $id, PriceWaterRepository $repository): JsonResponse
{
    $priceEau = $repository->find($id);

    $this->entityManager->remove($priceEau);
    $this->entityManager->flush();

    return new JsonResponse(['message' => 'Tarif eau supprimé avec succès.'], Response::HTTP_OK);
}
#[Route('/check', name: 'check_tranches_eau', methods: ['GET'])]
public function checkTranchesEau(PriceWaterRepository $repository): JsonResponse
{
    $allEntries = $repository->findAll();

    // Regrouper par tranche
    $counts = [];
    foreach ($allEntries as $entry) {
        $tranche = $entry->getTrancheEau();
        $counts[$tranche->value] = ($counts[$tranche->value] ?? 0) + 1;
    }

    // Vérifie les manquantes et les doublons
    $missing = [];
    $duplicates = [];

    foreach (TrancheEau::cases() as $trancheEnum) {
        $count = $counts[$trancheEnum->value] ?? 0;

        if ($count === 0) {
            $missing[] = $trancheEnum->value;
        } elseif ($count > 1) {
            $duplicates[] = $trancheEnum->value;
        }
    }

    return new JsonResponse([
        'message' => 'Vérification des tranches d’eau effectuée.',
        'missing' => $missing,
        'duplicates' => $duplicates,
        'is_complete' => empty($missing) && empty($duplicates)
    ]);
}
}