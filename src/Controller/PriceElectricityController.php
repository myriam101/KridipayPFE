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
use App\Repository\PriceWaterRepository;
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
#[Route('/edit/{id}', name: 'update_price_electric', methods: ['PUT'])]
public function updatePriceElectric(
    int $id,
    Request $request,
    PriceElectricityRepository $repository
): JsonResponse {
    $priceElectric = $repository->find($id);

    if (!$priceElectric) {
        return new JsonResponse(['message' => 'Tarif électricité introuvable.'], Response::HTTP_NOT_FOUND);
    }

    $data = json_decode($request->getContent(), true);

    try {
        // Conversion sécurisée en enum
        $newSector = isset($data['sector']) ? Sector::from($data['sector']) : $priceElectric->getSector();
        $newTranche = isset($data['tranche_elect']) ? TrancheElect::from($data['tranche_elect']) : $priceElectric->getTrancheElect();

    } catch (\ValueError $e) {
        return new JsonResponse(['message' => 'Secteur invalide.'], Response::HTTP_BAD_REQUEST);
    }

    $existing = $repository->findOneBy([
        'sector' => $newSector,
        'tranche_elect' => $newTranche,
    ]);

    if ($existing && $existing->getId() !== $id) {
        return new JsonResponse([
            'message' => 'Un tarif pour ce secteur et cette tranche existe déjà.'
        ], Response::HTTP_CONFLICT);
    }

    $priceElectric->setSector($newSector);
    $priceElectric->setTrancheElect($newTranche);

    if (isset($data['price'])) {
        $priceElectric->setPrice($data['price']);
    }

    $this->entityManager->flush();

    return new JsonResponse(['message' => 'Tarif électricité mis à jour avec succès.']);
}
  
#[Route('/all', name: 'all_elect', methods: ['GET'])]
    public function getAllElect(PriceElectricityRepository $pr): JsonResponse
    {
        $elect = $pr->findAll();

        return $this->json($elect, 200, [], ['groups' => 'elect:read']);
    }
   
#[Route('/{id}', name: 'delete_price_electric', methods: ['DELETE'])]
public function deletePriceElectric(int $id, EntityManagerInterface $em, PriceElectricityRepository $repository): JsonResponse
{
    $priceElectric = $repository->find($id);

    if (!$priceElectric) {
        return new JsonResponse(['message' => 'Tarif électricité introuvable.'], Response::HTTP_NOT_FOUND);
    }

    $em->remove($priceElectric);
    $em->flush();

    return new JsonResponse(['message' => 'Tarif électricité supprimé avec succès.'], Response::HTTP_OK);
}
#[Route('/check', name: 'check_electricity_prices', methods: ['GET'])]
public function checkElectricityPrices(PriceElectricityRepository $repository): JsonResponse
{
    $allRecords = $repository->findAll();

    $expectedSectors = Sector::cases(); // enum Sector
    $expectedTranches = TrancheElect::cases(); // enum TrancheElect

    $combinations = [];
    $duplicates = [];

    foreach ($allRecords as $record) {
        $key = $record->getSector()->value . '-' . $record->getTrancheElect()->value;

        if (isset($combinations[$key])) {
            $duplicates[] = [
                'sector' => $record->getSector()->value,
                'tranche' => $record->getTrancheElect()->value,
                'id' => $record->getId()
            ];
        } else {
            $combinations[$key] = true;
        }
    }

    $missing = [];

    foreach ($expectedSectors as $sector) {
        foreach ($expectedTranches as $tranche) {
            $key = $sector->value . '-' . $tranche->value;
            if (!isset($combinations[$key])) {
                $missing[] = [
                    'sector' => $sector->value,
                    'tranche' => $tranche->value,
                ];
            }
        }
    }

    return new JsonResponse([
        'missing_combinations' => $missing,
        'duplicates' => $duplicates,
        'status' => count($missing) === 0 && count($duplicates) === 0 ? 'ok' : 'incomplete',
    ]);
}

}