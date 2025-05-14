<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProductRepository;
use App\Repository\CarbonRepository;
use Psr\Log\LoggerInterface;
use App\Entity\EnergyBill;
use App\Entity\Simulation;
use App\Entity\Enum\BillCategory;
use App\Entity\Enum\PeriodeUse;
use App\Entity\Enum\TrancheEau;
use App\Entity\Enum\Sector;
use App\Entity\Enum\TrancheElect;
use App\Repository\EnergyBillRepository;
use App\Repository\PriceElectricityRepository;
use App\Repository\SimulationRepository;
use App\Repository\PriceWaterRepository;

#[Route('/EnergyBill')]

class EnergyBillController extends AbstractController
{
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;

    }
  #[Route('/calculate-bill/{id}', name: 'calculate_energy_bill', methods: ['POST'])]
public function calculateEnergyBill(
    int $id,
    SimulationRepository $simulationRepository,
    PriceElectricityRepository $electricityRepository,
    PriceWaterRepository $waterRepository,
    EnergyBillRepository $energyBillRepository
): JsonResponse {
    $simulation = $simulationRepository->find($id);
    if (!$simulation) {
        return new JsonResponse(['error' => 'Simulation not found'], 404);
    }

    $periode = $simulation->getPeriodeUse();
    $kwh = $simulation->getResultKhw(); // kWh total pour la période
    $litres = $simulation->getResultlt(); // litres total pour la période
    $m3 = $litres / 1000;

    // Vérifie s'il existe déjà une facture
    $existing = $energyBillRepository->findOneBy(['simulation' => $simulation]);
    if ($existing) {
        return new JsonResponse(['error' => 'Une facture existe déjà pour cette simulation.'], 409);
    }

    // Adaptation de la consommation d'électricité selon la période
    if ($periode === Simulation::THREE_MONTHS) {
        $kwh /= 3; // Pour une période trimestrielle, on divise par 3
    } elseif ($periode === Simulation::YEAR) {
        $kwh /= 12; // Pour une période annuelle, on divise par 12
    }

    // Déterminer la tranche d'électricité
    $tarifElect = null;
    foreach (TrancheElect::cases() as $tranche) {
        [$min, $max] = explode('-', str_replace('+', '', $tranche->value) . '-' . PHP_INT_MAX);
        if ($kwh >= (int)$min && $kwh <= (int)$max) {
            $tarifElect = $electricityRepository->findOneBy(['tranche_elect' => $tranche]);
            break;
        }
    }

    // Calcul du montant électricité
$montantElectricite = $tarifElect ? $kwh * $tarifElect->getPrice() : 0;

    // Adaptation de la consommation d'eau selon la période
    if ($periode === Simulation::MONTH) {
        $litres *= 3; // Pour une période mensuelle, on multiplie par 3 (pour obtenir la consommation trimestrielle)
    } elseif ($periode === Simulation::YEAR) {
        $litres /= 4; // Pour une période annuelle, on divise par 4 pour obtenir la consommation trimestrielle
    }
    $m3 = $litres / 1000;

    // Déterminer la tranche d'eau
    $tarifEau = null;
    foreach (TrancheEau::cases() as $tranche) {
        [$min, $max] = explode('-', str_replace('+', '', $tranche->value) . '-' . PHP_INT_MAX);
        if ($m3 >= (int)$min && $m3 <= (int)$max) {
            $tarifEau = $waterRepository->findOneBy(['tranche_eau' => $tranche]);
            break;
        }
    }

    // Calcul du montant eau
$montantEau = $tarifEau ? ($litres / 1000) * $tarifEau->getPrice() : 0;

    // Enregistrement de la facture
    $facture = new EnergyBill();
    $facture->setSimulation($simulation);
    $facture->setAmountElectr($montantElectricite);
    $facture->setAmountWater($montantEau);
    $facture->setAmountGaz(0); // à adapter si besoin
    $facture->setAmountBill($montantElectricite + $montantEau);
    if ($tarifElect) $facture->setPriceElectricity($tarifElect);
    if ($tarifEau) $facture->setPriceWater($tarifEau);

    $this->entityManager->persist($facture);
    $this->entityManager->flush();

    return new JsonResponse([
        'message' => 'Facture calculée et enregistrée avec succès',
        'montant_total' => $facture->getAmountBill(),
        'electricite' => $montantElectricite,
        'eau' => $montantEau,
        'periode' => $periode
    ]);
}

#[Route('/get/{simulationId}', name: 'get_energy_bill', methods: ['GET'])]
public function getEnergyBill(int $simulationId, EnergyBillRepository $billRepository): JsonResponse
{
    $bill = $billRepository->findOneBy(['simulation' => $simulationId]);

    if (!$bill) {
        return new JsonResponse(['error' => 'Aucune facture trouvée pour cette simulation.'], 404);
    }

    $simulation = $bill->getSimulation();

    return new JsonResponse([
        'montant_total' => $bill->getAmountBill(),
        'montant_electricite' => $bill->getAmountElectr(),
        'montant_eau' => $bill->getAmountWater()
        ]);
}



}