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
    
   
#[Route('/all', name: 'all_water', methods: ['GET'])]
    public function getAllWater(PriceWaterRepository $pr): JsonResponse
    {
        $water = $pr->findAll();

        return $this->json($water, 200, [], ['groups' => 'water:read']);
    }
    

}