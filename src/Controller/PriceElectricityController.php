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

    $required = ['tax', 'price_day', 'price_night', 'price_rush', 'periode_use', 'sector', 'tranche_elect'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            return new JsonResponse(['error' => "Missing field: $field"], 400);
        }
    }

    try {
        $periodeUse = PeriodeUse::from($data['periode_use']);
        $sector = Sector::from($data['sector']);
        $tranche = TrancheElect::from($data['tranche_elect']);
    } catch (\ValueError $e) {
        return new JsonResponse(['error' => 'Invalid enum value'], 400);
    }

    // 💡 Vérification déléguée au repository
    if ($repo->existsBySectorAndTranche($sector, $tranche)) {
        return new JsonResponse(['error' => 'Un tarif pour cette tranche et ce secteur existe déjà.'], 400);
    }

    $price = new PriceElectricity();
    $price->setTrancheElect($tranche);
    $price->setSector($sector);
    $price->setPeriodeUse($periodeUse);
    $price->setTax((float) $data['tax']);
    $price->setPriceDay((float) $data['price_day']);
    $price->setPriceNight((float) $data['price_night']);
    $price->setPriceRush((float) $data['price_rush']);

    $this->entityManager->persist($price);
    $this->entityManager->flush();

    return new JsonResponse([
        'success' => true,
        'id' => $price->getId(),
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
    $priceElectricity->setPriceDay($data['price_day']);
    $priceElectricity->setPriceNight($data['price_night']);
    $priceElectricity->setPriceRush($data['price_rush']);
    $priceElectricity->setTax($data['tax']);
    $priceElectricity->setPeriodeUse(PeriodeUse::from($data['periode_use']));

    $this->entityManager->flush();

    return new JsonResponse([
        'success' => true,
        'message' => 'Tarif mis à jour avec succès.'
    ]);
}


}