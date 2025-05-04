<?php
namespace App\Controller;

use App\Entity\Catalog;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\HelperService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ProductRepository;
use App\Repository\CarbonRepository;
use Psr\Log\LoggerInterface;
use App\Entity\EnergyBill;
use App\Entity\Simulation;
use App\Entity\Enum\BillCategory;
use App\Entity\Enum\PeriodeUse;
use App\Entity\Enum\Designation;
use App\Entity\Product;
use App\Entity\Productcatalog;
use App\Repository\CatalogRepository;
use App\Repository\CategoryRepository;
use App\Repository\PriceElectricityRepository;
use App\Repository\PriceGazRepository;
use App\Repository\PriceWaterRepository;
use App\Repository\ProviderRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/catalog')]

class CatalogController extends AbstractController
{

    private LoggerInterface $logger;

   private CatalogRepository $catalogRepository;
    private EntityManagerInterface $entityManager;
    private ProviderRepository $providerUserRepository;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger,ProviderRepository $providerUserRepository, CatalogRepository $catalogRepository)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->providerUserRepository= $providerUserRepository;
        $this->catalogRepository=$catalogRepository;

    }
    #[Route('/all', name: 'get_all_catalogs', methods: ['GET'])]
    public function getAllCatalogs(EntityManagerInterface $em): JsonResponse
    {
        $catalogs = $em->getRepository(Catalog::class)->findAll();

        $data = [];

        foreach ($catalogs as $catalog) {
            $data[] = [
                'id_catalog' => $catalog->getIdCatalog(),
                'name' => $catalog->getName()
                        ];
        }

        return new JsonResponse($data);
    }

   
    #[Route('/{catalogId}/categories', name: 'get_catalog_categories', methods: ['GET'])]
    public function getCategoriesByCatalog(int $catalogId ): JsonResponse
    {
        $categories = $this->catalogRepository->getCategoriesByCatalog($catalogId);

        $result = array_map(function ($category) {
            return [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'designation' => $category->getDesignation()->name,
            ];
        }, $categories);

        return new JsonResponse($result);
    }


    #[Route('/provider/{id}/catalogs', name: 'get_provider_catalogs', methods: ['GET'])]
    public function getCatalogsByProvider(int $id, CatalogRepository $catalogRepository): JsonResponse
    {
        $catalogs = $catalogRepository->findByProviderId($id);
    
        if (empty($catalogs)) {
            return new JsonResponse(['message' => 'Aucun catalogue trouvé pour ce fournisseur'], Response::HTTP_NOT_FOUND);
        }
    
        // Vous pouvez ici normaliser les données (ou utiliser un serializer si besoin)
        $data = array_map(function ($catalog) {
            return [
                'id' => $catalog->getIdCatalog(),
                'name' => $catalog->getName(),
            ];
        }, $catalogs);
    
        return new JsonResponse($data);
    }
}