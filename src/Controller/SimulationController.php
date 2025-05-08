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
    
        if (!$data || !isset($data['client_id'], $data['product_id'], $data['duration_use'], $data['nbr_use'])) {
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
    
    
        // 🔁 Appel à la méthode d’estimation
        $designation = $product->getIdCategory()->getDesignation();
        $feature = $product->getFeature();
    
        $estimated = $this->estimateEnergyConsumptionFromDesignation(
            $designation,
            $simulation->getNbrUse(),
            $simulation->getDurationUse(),
            $feature->getConsumptionWatt(),
            $feature->getConsumptionLiter(),
            $feature->getPower()
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
            );
    
            return [
                'id' => $simulation->getId(),
                'product_id' => $product->getId(),
                'product_name' => $product->getName(),
                'duration_use' => $simulation->getDurationUse(),
                'nbr_use' => $simulation->getNbrUse(),
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
    int $periodDays = 30
): array {
    $durationHours = $durationMinutes !== null ? $durationMinutes / 60 : 0;

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
            $kwh = ($consumptionPerCycle / 100) * $usagePerDay * $periodDays;
        }
        if ($consoLitres !== null) {
            $litres = ($consoLitres / 100) * $usagePerDay * $periodDays;
        }
    }
    

    // Par durée
    if (in_array($designation, $byDuration, true) && $powerWatts !== null && $durationMinutes !== null) {
        $kwh = ($powerWatts / 1000) * ($durationMinutes / 60) * $usagePerDay * $periodDays;

    }

    // Toujours actif
    if (in_array($designation, $alwaysOn, true) && $powerWatts !== null) {
        $totalHours = 24 * $periodDays;
        $kwh = ($powerWatts / 1000) * $totalHours;
    }

    return [
        'kwh' => round($kwh, 2),
        'litres' => round($litres, 2),
    ];
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