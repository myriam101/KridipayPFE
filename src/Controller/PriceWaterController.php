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

    #[Route('/calculate-bill/{simulationId}', name: 'calculate_bill')]
    public function calculateBill(int $simulationId, SimulationRepository $simulationRepo, EntityManagerInterface $em): JsonResponse
    {
       
        $simulation = $simulationRepo->find($simulationId);

        if (!$simulation) {
            return new JsonResponse(['error' => 'Simulation not found'], 404);
        }

        $product = $simulation->getProduct();
        $feature = $product->getFeature();

        if (!$feature) {
            return new JsonResponse(['error' => 'Feature not found for this product'], 404);
        }

        // Monthly consumption in m³ = liters * nbr_use * 30 (days) / 1000
        $litersPerUse = $feature->getConsumptionLiter();
        $nbrUsePerDay = $simulation->getNbrUse();
        $monthlyConsumption = ($litersPerUse * $nbrUsePerDay * 30) / 1000; // in m³

        // Determine TrancheEau and monthly price
        $tranche = null;
        $monthlyPrice = null;

        if ($monthlyConsumption <= 20 / 3) {
            $tranche = TrancheEau::ZERO_TWENTY;
            $monthlyPrice = 0.200 / 3;
        } elseif ($monthlyConsumption <= 40 / 3) {
            $tranche = TrancheEau::TWENTY_ONE_FORTY;
            $monthlyPrice = 0.740 / 3;
        } elseif ($monthlyConsumption <= 70 / 3) {
            $tranche = TrancheEau::FORTY_ONE_SEVENTY;
            $monthlyPrice = 1.040 / 3;
        } elseif ($monthlyConsumption <= 100 / 3) {
            $tranche = TrancheEau::SEVENTY_ONE_HUNDRED;
            $monthlyPrice = 1.490 / 3;
        } elseif ($monthlyConsumption <= 150 / 3) {
            $tranche = TrancheEau::HUNDRED_ONE_HUNDRED_FIFTY;
            $monthlyPrice = 1.770 / 3;
        } else {
            $tranche = TrancheEau::HUNDRED_FIFTY_PLUS;
            $monthlyPrice = 2.310 / 3;
        }

        // Create PriceWater
        $priceWater = new PriceWater();
        $priceWater->setTrancheEau($tranche);
        $priceWater->setPrice($monthlyPrice);

        $em->persist($priceWater);

        // Create EnergyBill
        $energyBill = new EnergyBill();
        $energyBill->setAmountWater($monthlyConsumption * $monthlyPrice);
        $energyBill->setAmountElectr(0);
        $energyBill->setAmountGaz(0);
        $energyBill->setAmountBill($energyBill->getAmountWater()); // total = only water for now
        $energyBill->setBillCategory(BillCategory::TRIMESTRE);
        $energyBill->setSimulation($simulation);
        $energyBill->setPriceWater($priceWater); // associate PriceWater

        $em->persist($energyBill);
        $this->entityManager->flush(); // this will save both entities, assigning IDs

        return new JsonResponse([
            'success' => true,
            'amount_water' => $energyBill->getAmountWater(),
            'tranche' => $tranche->value,
            'price_per_m3' => $monthlyPrice,
            'price_water_id' => $priceWater->getId(),
            'energy_bill_id' => $energyBill->getId(),
        ]);
    }
    
    #[Route('/add', name: 'add_price_water', methods: ['POST'])]
    public function addPriceWater(
        Request $request,
        PriceWaterRepository $repo,
        EntityManagerInterface $em
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
    
        $em->persist($priceWater);
        $this->entityManager->flush();
    
        return new JsonResponse([
            'success' => true,
            'id' => $priceWater->getId(),
            'tranche_eau' => $tranche->value,
            'price' => $priceWater->getPrice(),
        ]);
    }
    
    #[Route('/update/{id}', name: 'update_pricewater', methods: ['PUT'])]
    public function updatePriceWater(
        int $id,
        Request $request,
        PriceWaterRepository $repo    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['price']) || !isset($data['tranche_eau'])) {
            return new JsonResponse(['error' => 'Champs requis: price, tranche_eau'], 400);
        }
    
        $priceWater = $repo->find($id);
        if (!$priceWater) {
            return new JsonResponse(['error' => 'Tarif introuvable'], 404);
        }
    
        $tranche = TrancheEau::from($data['tranche_eau']);
    
        // Vérifie si une autre entrée existe déjà avec la même tranche
        $existing = $repo->findOneBy(['tranche_eau' => $tranche]);
    
        // Si une autre entrée existe avec cette tranche et ce n'est pas celle qu'on modifie
        if ($existing && $existing->getId() !== $priceWater->getId()) {
            return new JsonResponse(['error' => 'Un autre tarif avec cette tranche existe déjà.'], 400);
        }
    
        // Appliquer les nouvelles valeurs
        $priceWater->setPrice($data['price']);
        $priceWater->setTrancheEau($tranche);
    
        $this->entityManager->flush();
    
        return new JsonResponse([
            'success' => true,
            'id' => $priceWater->getId(),
            'new_price' => $priceWater->getPrice(),
            'tranche_eau' => $priceWater->getTrancheEau()->value,
        ]);
    }
    
   

    

}