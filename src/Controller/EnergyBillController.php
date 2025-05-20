<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use App\Entity\EnergyBill;
use App\Entity\Simulation;
use App\Entity\Enum\TrancheEau;
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
        return new JsonResponse(['error' => 'Simulation non trouvée'], 404);
    }

    $periode = $simulation->getPeriodeUse();
    $kwh = $simulation->getResultKhw();
    $litres = $simulation->getResultlt();
    $m3 = $litres / 1000;

    // Adaptation de la consommation d'électricité
    if ($periode === Simulation::THREE_MONTHS) {
        $kwh /= 3;
    } elseif ($periode === Simulation::YEAR) {
        $kwh /= 12;
    }

    // Tarif électricité
    $tarifElect = null;
    foreach (TrancheElect::cases() as $tranche) {
        [$min, $max] = explode('-', str_replace('+', '', $tranche->value) . '-' . PHP_INT_MAX);
        if ($kwh >= (int)$min && $kwh <= (int)$max) {
            $tarifElect = $electricityRepository->findOneBy(['tranche_elect' => $tranche]);
            break;
        }
    }

    $montantElectricite = $tarifElect ? $kwh * $tarifElect->getPrice() : 0;

    // Adaptation eau
    if ($periode === Simulation::MONTH) {
        $litres *= 3;
    } elseif ($periode === Simulation::YEAR) {
        $litres /= 4;
    }
    $m3 = $litres / 1000;

    // Tarif eau
    $tarifEau = null;
    foreach (TrancheEau::cases() as $tranche) {
        [$min, $max] = explode('-', str_replace('+', '', $tranche->value) . '-' . PHP_INT_MAX);
        if ($m3 >= (int)$min && $m3 <= (int)$max) {
            $tarifEau = $waterRepository->findOneBy(['tranche_eau' => $tranche]);
            break;
        }
    }

    $montantEau = $tarifEau ? $m3 * $tarifEau->getPrice() : 0;

    // Ajout ou mise à jour
    $facture = $energyBillRepository->findOneBy(['simulation' => $simulation]);
    if (!$facture) {
        $facture = new EnergyBill();
        $facture->setSimulation($simulation);
    }

    $facture->setAmountElectr($montantElectricite);
    $facture->setAmountWater($montantEau);
    $facture->setAmountGaz(0);
    $facture->setAmountBill($montantElectricite + $montantEau);
    if ($tarifElect) $facture->setPriceElectricity($tarifElect);
    if ($tarifEau) $facture->setPriceWater($tarifEau);

    $this->entityManager->persist($facture);
    $this->entityManager->flush();

    return new JsonResponse([
        'message' => 'Facture enregistrée avec succès' . ($facture->getId() ? ' (mise à jour)' : ''),
        'montant_total' => $facture->getAmountBill(),
        'electricite' => $montantElectricite,
        'eau' => $montantEau,
        'periode' => $periode,
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

#[Route('/get-cart-bills/{clientId}', name: 'get_cart_bills', methods: ['GET'])]
public function getCartBills(
    int $clientId,
    SimulationRepository $simulationRepo,
    EnergyBillRepository $billRepo
): JsonResponse {
    $simulations = $simulationRepo->findBy(['client' => $clientId]);
    $result = [];

    foreach ($simulations as $sim) {
        $bill = $billRepo->findOneBy(['simulation' => $sim]);
        if ($bill) {
            $result[] = [
                'product_id' => $sim->getProduct()->getId(),
                'amount_bill' => $bill->getAmountBill(),
                'amount_electricity' => $bill->getAmountElectr(),
                'amount_water' => $bill->getAmountWater(),
                'periode' => $sim->getPeriodeUse()
            ];
        }
    }

    return new JsonResponse(['bills' => $result]);
}
#[Route('/calculate-bills', name: 'calculate_multiple_energy_bills', methods: ['POST'])]
public function calculateMultipleEnergyBills(
    Request $request,
    SimulationRepository $simulationRepository,
    PriceElectricityRepository $electricityRepository,
    PriceWaterRepository $waterRepository,
    EnergyBillRepository $energyBillRepository,
): JsonResponse {
    $data = json_decode($request->getContent(), true);
    $simulationIds = $data['simulation_ids'] ?? [];

    if (empty($simulationIds) || !is_array($simulationIds)) {
        return new JsonResponse(['error' => 'Liste d\'ID de simulations manquante ou invalide'], 400);
    }

    $results = [];

    foreach ($simulationIds as $id) {
        $simulation = $simulationRepository->find($id);
        if (!$simulation) {
            $results[] = ['id' => $id, 'error' => 'Simulation non trouvée'];
            continue;
        }

        $periode = $simulation->getPeriodeUse();
        $kwh = $simulation->getResultKhw();
        $litres = $simulation->getResultlt();

        // Ajustement selon période
        if ($periode === Simulation::THREE_MONTHS) $kwh /= 3;
        elseif ($periode === Simulation::YEAR) $kwh /= 12;

        // Tarif électricité
        $tarifElect = null;
        foreach (TrancheElect::cases() as $tranche) {
            [$min, $max] = explode('-', str_replace('+', '', $tranche->value) . '-' . PHP_INT_MAX);
            if ($kwh >= (int)$min && $kwh <= (int)$max) {
                $tarifElect = $electricityRepository->findOneBy(['tranche_elect' => $tranche]);
                break;
            }
        }
$montantElectricite = $tarifElect ? round($kwh * $tarifElect->getPrice()) : 0;

        // Ajustement eau
        if ($periode === Simulation::MONTH) $litres *= 3;
        elseif ($periode === Simulation::YEAR) $litres /= 4;
        $m3 = $litres / 1000;

        // Tarif eau
        $tarifEau = null;
        foreach (TrancheEau::cases() as $tranche) {
            [$min, $max] = explode('-', str_replace('+', '', $tranche->value) . '-' . PHP_INT_MAX);
            if ($m3 >= (int)$min && $m3 <= (int)$max) {
                $tarifEau = $waterRepository->findOneBy(['tranche_eau' => $tranche]);
                break;
            }
        }
$montantEau = $tarifEau ? round($m3 * $tarifEau->getPrice()) : 0;

        // Création ou mise à jour de la facture
        $facture = $energyBillRepository->findOneBy(['simulation' => $simulation]) ?? new EnergyBill();
        $facture->setSimulation($simulation);
        $facture->setAmountElectr($montantElectricite);
        $facture->setAmountWater($montantEau);
        $facture->setAmountGaz(0);
        $facture->setAmountBill($montantElectricite + $montantEau);
        if ($tarifElect) $facture->setPriceElectricity($tarifElect);
        if ($tarifEau) $facture->setPriceWater($tarifEau);

        $this->entityManager->persist($facture);

        $results[] = [
            'id_simulation' => $simulation->getId(),
            'montant_total' => $facture->getAmountBill(),
            'electricite' => $montantElectricite,
            'eau' => $montantEau,
            'periode' => $periode,
        ];
    }

    $this->entityManager->flush();

    return new JsonResponse([
        'message' => 'Factures traitées avec succès',
        'results' => $results,
    ]);
}


}