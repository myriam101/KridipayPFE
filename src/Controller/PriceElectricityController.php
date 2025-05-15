<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\PriceElectricityRepository;
use Psr\Log\LoggerInterface;
use App\Entity\Enum\PeriodeUse;
use App\Entity\Enum\TrancheElect;
use App\Entity\Enum\Sector;
use App\Entity\PriceElectricity;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/PriceElectricity')]
class PriceElectricityController extends AbstractController
{

    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger, HttpClientInterface $httpClient)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;

    }
    #[Route('/add', name: 'add_electricity_price', methods: ['POST'])]
public function addElectricityPrice(
    Request $request,
    PriceElectricityRepository $repo
): JsonResponse {
    $data = json_decode($request->getContent(), true);

    if (!$data) {
        return new JsonResponse(['error' => 'Invalid JSON'], 400);
    }

    $required = ['price', 'sector', 'tranche_elect'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            return new JsonResponse(['error' => "Missing field: $field"], 400);
        }
    }

    try {
        $sector = Sector::from($data['sector']);
        $tranche = TrancheElect::from($data['tranche_elect']);
    } catch (\ValueError $e) {
        return new JsonResponse(['error' => 'Invalid enum value'], 400);
    }

    // 💡 Vérification déléguée au repository
    if ($repo->existsBySectorAndTranche($sector, $tranche)) {
        return new JsonResponse(['error' => 'Un tarif pour cette tranche et ce secteur existe déjà.'], 400);
    }

    $price_elect = new PriceElectricity();
    $price_elect->setTrancheElect($tranche);
    $price_elect->setSector($sector);
    $price_elect->setPrice($data['price']);
    $this->entityManager->persist($price_elect);
    $this->entityManager->flush();

    return new JsonResponse([
        'success' => true,
        'id' => $price_elect->getId(),
        'message' => 'Tarif électricité ajouté avec succès.'
    ]);
}
#[Route('/update/{id}', name: 'update_price_electricity', methods: ['PUT'])]
public function updatePriceElectricity(
    int $id,
    Request $request,
    PriceElectricityRepository $repo): JsonResponse {
    $priceElectricity = $repo->find($id);

    if (!$priceElectricity) {
        return new JsonResponse(['error' => 'Tarif non trouvé.'], 404);
    }

    $data = json_decode($request->getContent(), true);

    $tranche = TrancheElect::from($data['tranche']);
    $sector = Sector::from($data['sector']);

    // Vérifie si un autre tarif avec la même tranche et secteur existe déjà
    $existing = $repo->findOneByTrancheAndSector($tranche, $sector);

    if ($existing && $existing->getId() !== $id) {
        return new JsonResponse(['error' => 'Un tarif avec cette tranche et ce secteur existe déjà.'], 409);
    }

    $priceElectricity->setTrancheElect($tranche);
    $priceElectricity->setSector($sector);
    $priceElectricity->setPrice($data['price']);

    $this->entityManager->flush();

    return new JsonResponse([
        'success' => true,
        'message' => 'Tarif mis à jour avec succès.'
    ]);
}

  
#[Route('/all', name: 'all_elect', methods: ['GET'])]
    public function getAllElect(PriceElectricityRepository $pr): JsonResponse
    {
        $elect = $pr->findAll();

        return $this->json($elect, 200, [], ['groups' => 'elect:read']);
    }
}