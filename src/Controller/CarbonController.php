<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProductRepository;
use App\Repository\CarbonRepository;
use Psr\Log\LoggerInterface;
use App\Entity\Carbon;
use App\Entity\Enum\Badge;

class CarbonController extends AbstractController
{
    private LoggerInterface $logger;


    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;

    }

    //displays the carbon only if visible set to 1
    #[Route('/carbon/{productId}', name: 'app_carbon_get', methods: ['GET'])]
    public function getCarbon(string $productId, CarbonRepository $carbonRepository, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find((int) $productId); // Convert to integer
    
        if (!$product) {
            return new Response('Product not found', 404);
        }
    
        $carbon = $carbonRepository->findOneBy(['product' => $product]);
    
        if (!$carbon) {
            return new Response('Carbon footprint not found', 404);
        }
        // Check if "visible" is false (0), return no content
        if (!$carbon->isVisible()) {
            return new Response(null, 204);
        }
        // If "visible" is true (1), return the carbon footprint details
        return $this->json([
            'product_id' => $carbon->getProduct()->getId(),
            'value' => $carbon->getValue(),
            'date_add' => $carbon->getDateAdd()->format('Y-m-d H:i:s'),
            'date_update' => $carbon->getDateUpdate()->format('Y-m-d H:i:s'),
            'visible' => $carbon->isVisible()
        ]);
    }
    
//adds carbon to a specified product , par defaut visible manual add
#[Route('/carbon/add', name: 'app_carbon_add', methods: ['POST'])]
public function addCarbon(Request $request, CarbonRepository $carbonRepository, ProductRepository $productRepository): Response
{
    $productId = $request->headers->get('product_id');
    $data = json_decode($request->getContent(), true);

    $value = $data['value'] ?? null;
    $visible = $data['visible'] ?? true;

    if (!$productId || !$value) {
        return new Response('Missing required fields', 400);
    }

    $product = $productRepository->find($productId);

    if (!$product) {
        return new Response('Product not found', 404);
    }
    //Add carbon footprint
    $carbon = $carbonRepository->addCarbonFootprint($product, (float) $value, (bool) $visible);

    return $this->json([
        'product_id' => $carbon->getProduct()->getId(),
        'value' => $carbon->getValue(),
        'date_add' => $carbon->getDateAdd()->format('Y-m-d H:i:s'),
        'date_update' => $carbon->getDateUpdate()->format('Y-m-d H:i:s'),
        'visible' => $carbon->isVisible()
    ]);
}
//automatique calcul add
#[Route('/carbon/add2', name: 'app_carbon_add2', methods: ['POST'])]
public function addCarbon2(Request $request, CarbonRepository $carbonRepository, ProductRepository $productRepository): Response
{
    $productId = $request->headers->get('product_id');
    $data = json_decode($request->getContent(), true);

    // Retrieve the visibility parameter or set to true if not provided
    $visible = $data['visible'] ?? true;

    if (!$productId) {
        return new Response('Missing required fields', 400);
    }

    // Find the product using the provided productId
    $product = $productRepository->find($productId);
    if (!$product) {
        return new Response('Product not found', 404);
    }
    // Calculate the carbon impact using the repository method
    $carbonImpact = $carbonRepository->calculateCarbonImpactByProductId($productId);

    if ($carbonImpact === 0) {
        return new Response('Unable to calculate carbon impact for this product', 400);
    }

    // Add carbon footprint based on the calculated carbon impact and set the factor value
    $carbon = $carbonRepository->addCarbonFootprint($product, $carbonImpact, (bool) $visible);

    return $this->json([
        'product_id' => $carbon->getProduct()->getId(),
        'value' => $carbon->getValue(),
        'date_add' => $carbon->getDateAdd()->format('Y-m-d H:i:s'),
        'date_update' => $carbon->getDateUpdate()->format('Y-m-d H:i:s'),
        'visible' => $carbon->isVisible()
        ]);
}

// updates carbon value of specified product
#[Route('/carbon/update', name: 'app_carbon_update', methods: ['PUT'])]
public function updateCarbon(Request $request, CarbonRepository $carbonRepository, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
{
    $productId = $request->headers->get('product_id');
    $data = json_decode($request->getContent(), true);

    $value = $data['value'] ?? null;
    $visible = $data['visible'] ?? null;

    if (!$productId || $value === null) {
        return new Response('Missing required fields', 400);
    }

    $product = $productRepository->find($productId);

    if (!$product) {
        return new Response('Product not found', 404);
    }

    $carbon = $carbonRepository->findOneBy(['product' => $product]);

    if (!$carbon) {
        return new Response('Carbon footprint not found', 404);
    }

    $carbon->setValue((float) $value);
    if ($visible !== null) {
        $carbon->setVisible((bool) $visible);
    }
    $carbon->setDateUpdate(new \DateTime());

    $entityManager->persist($carbon);
    $entityManager->flush();

    return $this->json([
        'product_id' => $carbon->getProduct()->getId(),
        'value' => $carbon->getValue(),
        'date_add' => $carbon->getDateAdd()->format('Y-m-d H:i:s'),
        'date_update' => $carbon->getDateUpdate()->format('Y-m-d H:i:s'),
        'visible' => $carbon->isVisible()
    ]);
}

#[Route('/carbon/notvisible', name: 'app_carbon_set_all_visible_zero', methods: ['POST'])]
public function setAllCarbonVisibleToZero(CarbonRepository $carbonRepository): Response
{
    $carbonRepository->setAllCarbonVisibleToZero();
    return new Response('All carbon footprints visible values have been set to 0');
}
#[Route('/carbon/visible', name: 'app_carbon_set_all_visible_one', methods: ['POST'])]
public function setAllCarbonVisibleToOne(CarbonRepository $carbonRepository): Response
{
    $carbonRepository->setAllCarbonVisibleToOne();
    return new Response('All carbon footprints visible values have been set to 1');
}
        
}