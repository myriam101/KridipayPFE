<?php
namespace App\Controller;

use App\Entity\Enum\Designation;
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
use App\Repository\EnergyBillRepository;
use App\Repository\PriceElectricityRepository;
use App\Repository\PriceWaterRepository;
use App\Service\Service;
use App\Service\servicefacture;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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
        $data = json_decode($request->getContent(), true);
    
        if (!$data || !isset($data['client_id'], $data['product_id'], $data['duration_use'], $data['nbr_use'],$data['periode_use'])) {
            return new Response(json_encode(['message' => 'Missing data']), 400, ['Content-Type' => 'application/json']);
        }
    
        $client = $clientRepo->find($data['client_id']);
        $product = $productRepo->find($data['product_id']);
    
        if (!$client || !$product) {
            return new Response(json_encode(['message' => 'Client or Product not found']), 404, ['Content-Type' => 'application/json']);
        }
    
        $simulation = new Simulation();
        $simulation->setProduct($product);
        $simulation->setIdClient($client);
        $simulation->setNbrUse((int)$data['nbr_use']);
        $simulation->setDurationUse((int)$data['duration_use']);
        $simulation->setPeriodeUse((string)$data['periode_use']);
    
    
        //Appel à la méthode d’estimation
        $designation = $product->getIdCategory()->getDesignation();
        $feature = $product->getFeature();
    
        $estimated = $this->estimateEnergyConsumptionFromDesignation(
            $designation,
            $simulation->getNbrUse(),
            $simulation->getDurationUse(),
            $feature->getConsumptionWatt(),
            $feature->getConsumptionLiter(),
            $feature->getPower(),
            $simulation->getPeriodeUse()
        );
        $simulation->setResultKhw($estimated['kwh']);
        $simulation->setResultlt($estimated['litres']);


        $em->persist($simulation);
        $em->flush();
        //  Réponse avec estimation
        $responseData = [
            'simulation_id' => $simulation->getId(),
            'product_id' => $product->getId(),
            'client_id' => $client->getId(),
            'duration_use' => $simulation->getDurationUse(),
            'nbr_use' => $simulation->getNbrUse(),
            'designation'=>$product->getIdCategory()->getDesignation(),
            'periode_use'=>$simulation->getPeriodeUse(),
            'estimated_consumption' => $estimated // <-- kWh et litres
        ];
    
        return new Response(json_encode($responseData), 201, ['Content-Type' => 'application/json']);
    }
    
    
    #[Route('/all/{client_id}', name: 'get_simulations_for_client', methods: ['GET'])]
    public function getSimulationsForClient(
        int $client_id,
        ClientRepository $clientRepo,
        SimulationRepository $simulationRepo
    ): Response {
        $client = $clientRepo->find($client_id);
    
        if (!$client) {
            return new Response(json_encode(['message' => 'Client not found']), 404, ['Content-Type' => 'application/json']);
        }
    
        $simulations = $simulationRepo->findBy(['client' => $client]);
    
        if (!$simulations) {
            return new Response(json_encode(['message' => 'No simulations found for this client']), 404, ['Content-Type' => 'application/json']);
        }
    
        $simulationData = array_map(function ($simulation) {
            $product = $simulation->getProduct();
            $designation = $product->getIdCategory()->getDesignation();
            $consumptionPerCycle = $product->getConsumptionWatt(); // kWh
            $consoLitres=$product->getConsumptionLiter();
            $powerWatts = $product->getPower(); // W
    
            $estimatedKWh = $this->estimateEnergyConsumptionFromDesignation(
                $designation,
                $simulation->getNbrUse(),
                $simulation->getDurationUse(),
                $product->getFeature()->getConsumptionWatt(),
                $product->getFeature()->getConsumptionLiter(),
                $product->getFeature()->getPower(),
                $simulation->getPeriodeUse()
            );
    
            return [
                'id' => $simulation->getId(),
                'product_id' => $product->getId(),
                'product_name' => $product->getName(),
                'duration_use' => $simulation->getDurationUse(),
                'nbr_use' => $simulation->getNbrUse(),
                'periode_use'=>$simulation->getPeriodeUse(),
                'estimated_kWh' => $estimatedKWh
            ];
        }, $simulations);
    
        return new Response(json_encode([
            'client_id' => $client_id,
            'simulations' => $simulationData,
        ]), 200, ['Content-Type' => 'application/json']);
    }
    

public function estimateEnergyConsumptionFromDesignation(
    Designation $designation,
    int $usagePerDay,
    ?int $durationMinutes,
    ?float $consumptionPerCycle,
    ?float $consoLitres,
    ?float $powerWatts,
string $periodKey
    ): array {
    $durationHours = $durationMinutes !== null ? $durationMinutes / 60 : 0;
// Vérifier que periodKey est valide
    if (is_null($periodKey)) {
        throw new \InvalidArgumentException("La période est invalide.");
    }

    // Utilise la fonction pour obtenir les jours
    $periodInDays = $this->getDaysFromPeriod($periodKey);
    // Designations avec consommation par cycle (eau + énergie)
    $byCycle = [
        Designation::LAVE_LINGE,
        Designation::SECHE_LINGE,
        Designation::LAVANTE_SECHANTE,
        Designation::LAVE_VAISSELLE,
        Designation::FOUR
    ];

    // Designations avec consommation par durée (énergie uniquement)
    $byDuration = [
        Designation::CLIMATISEUR,
        Designation::TV,
        Designation::ASPIRATEUR,
        Designation::CHAUFFAGE,
        Designation::CHAUFFE_EAU,
        Designation::CHAUDIERE
    ];

    // Designations avec consommation continue (24h/24) - énergie uniquement
    $alwaysOn = [
        Designation::REFRIGERATEUR,
        Designation::CONGELATEUR,
        Designation::CAVE_A_VIN
    ];

    $kwh = 0.0;
    $litres = 0.0;

    // Par cycle
    if (in_array($designation, $byCycle, true)) {
        if ($consumptionPerCycle !== null) {
            // consommation pour 100 cycles → on divise par 100
            $kwh = ($consumptionPerCycle / 100) * $usagePerDay * $periodInDays;
        }
         // consommation pour 1 cycle

        if ($consoLitres !== null) {
            $litres = ($consoLitres) * $usagePerDay * $periodInDays;
        }
    }
    

    // Par durée
    if (in_array($designation, $byDuration, true) && $powerWatts !== null && $durationMinutes !== null) {
        $kwh = ($powerWatts / 1000) * ($durationMinutes / 60) * $usagePerDay * $periodInDays;

    }

    // Toujours actif
    if (in_array($designation, $alwaysOn, true) && $powerWatts !== null) {
        $totalHours = 24 * $periodInDays;
        $kwh = ($powerWatts / 1000) * $totalHours;
    }

    return [
        'kwh' => round($kwh, 2),
        'litres' => round($litres, 2),
    ];
}
public function getDaysFromPeriod(string $period): int
{
    return match ($period) {
        Simulation::MONTH => 30,
        Simulation::THREE_MONTHS => 90,
        Simulation::YEAR => 365
        };
}

#[Route('/simulation/{id}/water-bill', name: 'app_simulation_water_bill', methods: ['GET'])]
public function calculateWaterBill(
    Simulation $simulation,
    Request $request,
    SimulationRepository $simulationRepository
): JsonResponse {
    $periode = $request->query->get('periode', 'mois');

    $amount = $simulationRepository->calculateWaterBill($simulation);

    return $this->json([
        'montant_facture_eau' => round($amount, 2),
    ]);
}

#[Route('/add-or-update', name: 'app_simulation_add_or_update', methods: ['POST'])]
public function addOrUpdateSimulation(Request $request, EntityManagerInterface $em, SimulationRepository $simulationRepo, ProductRepository $productRepo, ClientRepository $clientRepo): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $clientId = $data['client_id'] ?? null;
    $productId = $data['product_id'] ?? null;

    if (!$clientId || !$productId) {
        return new JsonResponse(['message' => 'Client ou produit manquant.'], 400);
    }

    $client = $clientRepo->find($clientId);
    $product = $productRepo->find($productId);

    if (!$client || !$product) {
        return new JsonResponse(['message' => 'Client ou produit introuvable.'], 404);
    }

    // Rechercher une simulation existante
    $existingSimulation = $simulationRepo->findOneBy([
        'client' => $client,
        'product' => $product,
    ]);

    if ($existingSimulation) {
        // Mise à jour de la simulation existante
        $existingSimulation->setDurationUse($data['duration_use']);
        $existingSimulation->setNbrUse($data['nbr_use']);
        $existingSimulation->setPeriodeUse($data['periode_use']);

        $simulation = $existingSimulation;
        //Appel à la méthode d’estimation
        $designation = $product->getIdCategory()->getDesignation();
        $feature = $product->getFeature();
    
        $estimated = $this->estimateEnergyConsumptionFromDesignation(
            $designation,
            $simulation->getNbrUse(),
            $simulation->getDurationUse(),
            $feature->getConsumptionWatt(),
            $feature->getConsumptionLiter(),
            $feature->getPower(),
            $simulation->getPeriodeUse()
        );
        $simulation->setResultKhw($estimated['kwh']);
        $simulation->setResultlt($estimated['litres']);
    } else {
        // Création d'une nouvelle simulation
        $simulation = new Simulation();
        $simulation->setIdClient($client);
        $simulation->setProduct($product);
        $simulation->setDurationUse($data['duration_use']);
        $simulation->setNbrUse($data['nbr_use']);
        $simulation->setPeriodeUse($data['periode_use']);
//Appel à la méthode d’estimation
        $designation = $product->getIdCategory()->getDesignation();
        $feature = $product->getFeature();
    
        $estimated = $this->estimateEnergyConsumptionFromDesignation(
            $designation,
            $simulation->getNbrUse(),
            $simulation->getDurationUse(),
            $feature->getConsumptionWatt(),
            $feature->getConsumptionLiter(),
            $feature->getPower(),
            $simulation->getPeriodeUse()
        );
        $simulation->setResultKhw($estimated['kwh']);
        $simulation->setResultlt($estimated['litres']);
        $em->persist($simulation);
    }

    $em->flush();

    // Retourner la simulation (avec ID)
    return new JsonResponse([
        'id' => $simulation->getId(),
                    'estimated_consumption' => $estimated // <-- kWh et litres

    ], 200);
}
#[Route('/bulk', name: 'app_simulation_bulk_only', methods: ['POST'])]
public function bulkAddOrUpdateSimulationsOnly(
    Request $request,
    SimulationRepository $simulationRepo,
    ProductRepository $productRepo,
    ClientRepository $clientRepo
): JsonResponse {
    $data = json_decode($request->getContent(), true);
    $clientId = $data['client_id'] ?? null;
    $simulations = $data['simulations'] ?? [];
    $periode = $data['periode_use'] ?? null;

    if (!$clientId || empty($simulations) || !$periode) {
        return new JsonResponse(['message' => 'Données manquantes.'], 400);
    }

    $client = $clientRepo->find($clientId);
    if (!$client) {
        return new JsonResponse(['message' => 'Client introuvable.'], 404);
    }

    $results = [];

    foreach ($simulations as $simData) {
        $productId = $simData['product_id'] ?? null;
        $duration = $simData['duration_use'] ?? null;
        $nbr = $simData['nbr_use'] ?? null;

        if (!$productId || !$duration || !$nbr) {
            continue;
        }

        $product = $productRepo->find($productId);
        if (!$product) {
            continue;
        }

        $simulation = $simulationRepo->findOneBy([
            'client' => $client,
            'product' => $product
        ]) ?? new Simulation();

        $simulation->setIdClient($client);
        $simulation->setProduct($product);
        $simulation->setDurationUse($duration);
        $simulation->setNbrUse($nbr);
        $simulation->setPeriodeUse($periode);

        // Estimation
        $feature = $product->getFeature();
        $designation = $product->getIdCategory()->getDesignation();

        $estimated = $this->estimateEnergyConsumptionFromDesignation(
            $designation,
            $nbr,
            $duration,
            $feature->getConsumptionWatt(),
            $feature->getConsumptionLiter(),
            $feature->getPower(),
            $periode
        );

        $simulation->setResultKhw($estimated['kwh']);
        $simulation->setResultlt($estimated['litres']);

        $this->entityManager->persist($simulation);

        $results[] = [
            'product_id' => $product->getId(),
            'simulation_id' => $simulation->getId(),
            'estimated' => $estimated,
        ];
    }

    $this->entityManager->flush();

    return new JsonResponse([
        'message' => 'Simulations traitées avec succès.',
        'results' => $results,
    ]);
}



}