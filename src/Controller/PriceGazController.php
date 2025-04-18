<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\PriceGaz;
use App\Repository\PriceGazRepository;
use App\Entity\Enum\TrancheGaz;
use App\Entity\Enum\Sector;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;



#[Route('/PriceGaz')]
class PriceGazController extends AbstractController
{
    private LoggerInterface $logger;


    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;

    }

#[Route('/add', name: 'add_price_gaz', methods: ['POST'])]
public function addPriceGaz(Request $request, EntityManagerInterface $em, PriceGazRepository $priceGazRepo): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $tranche = TrancheGaz::from($data['tranche_gaz']);
    $sector = Sector::from($data['sector']);
    $price = $data['price'];

    // Vérifie s'il existe déjà une combinaison tranche + secteur
    $existing = $priceGazRepo->findOneBy(['tranche_gaz' => $tranche, 'sector' => $sector]);

    if ($existing) {
        return new JsonResponse(['error' => 'Tarif déjà existant pour cette tranche et ce secteur'], 400);
    }

    $priceGaz = new PriceGaz();
    $priceGaz->setTrancheGaz($tranche);
    $priceGaz->setSector($sector);
    $priceGaz->setPrice($price);

    $em->persist($priceGaz);
    $em->flush();

    return new JsonResponse([
        'success' => true,
        'id' => $priceGaz->getId(),
        'tranche' => $priceGaz->getTrancheGaz()->value,
        'sector' => $priceGaz->getSector()->value,
        'price' => $priceGaz->getPrice(),
    ]);
}
#[Route('/update/{id}', name: 'update_price_gaz', methods: ['PUT'])]
public function updatePriceGaz(
    int $id,
    Request $request,
    PriceGazRepository $priceGazRepo
): JsonResponse {
    $existingGaz = $priceGazRepo->find($id);

    if (!$existingGaz) {
        return new JsonResponse(['error' => 'Tarif Gaz non trouvé'], 404);
    }

    $data = json_decode($request->getContent(), true);
    $newTranche = TrancheGaz::from($data['tranche_gaz']);
    $newSector = Sector::from($data['sector']);
    $newPrice = (float) $data['price'];

    // Vérifier s'il existe déjà un autre enregistrement avec la même tranche + secteur
    $conflict = $priceGazRepo->findOneBy([
        'tranche_gaz' => $newTranche,
        'sector' => $newSector,
    ]);

    if ($conflict && $conflict->getId() !== $id) {
        return new JsonResponse(['error' => 'Un tarif avec cette tranche et ce secteur existe déjà.'], 400);
    }

    $existingGaz->setTrancheGaz($newTranche);
    $existingGaz->setSector($newSector);
    $existingGaz->setPrice($newPrice);
    $this->entityManager->flush();
    return new JsonResponse([
        'success' => true,
        'message' => 'Tarif Gaz modifié avec succès',
        'id' => $existingGaz->getId()
    ]);
}

}