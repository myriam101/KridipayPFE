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
    
//adds carbon to a specified product , par defaut visible
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
        'message' => 'Carbon footprint added successfully',
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

    // Persist & Flush
    $entityManager->persist($carbon);
    $entityManager->flush();

    return $this->json([
        'message' => 'Carbon footprint updated successfully',
        'product_id' => $carbon->getProduct()->getId(),
        'value' => $carbon->getValue(),
        'date_add' => $carbon->getDateAdd()->format('Y-m-d H:i:s'),
        'date_update' => $carbon->getDateUpdate()->format('Y-m-d H:i:s'),
        'visible' => $carbon->isVisible()
    ]);
}
#[Route('/carbon/calculate/{productId}', name: 'app_carbon_calculate', methods: ['POST'])]
public function calculateCarbon(int $productId, CarbonRepository $carbonRepository, ProductRepository $productRepository): Response
{
    $product = $productRepository->find($productId);

    if (!$product) {
        return new Response('Product not found', 404);
    }

    $feature = $product->getFeature(); // OneToOne relation
    if (!$feature) {
        return new Response('Features not found for this product', 404);
    }

    // Déterminer la catégorie
    $category = $product->getIdCategory();
    if (!$category) {
        return new Response('Category not found', 404);
    }

    // Calcul de l'empreinte carbone
    $carbonValue = $carbonRepository->calculateCarbonValue($category->getDesignation(), $feature);

    // Ajouter ou mettre à jour l'empreinte carbone
    $carbon = $carbonRepository->findOneBy(['product' => $product]) ?? new Carbon();
    $carbon->setProduct($product);
    $carbon->setValue($carbonValue);
    $carbon->setDateUpdate(new \DateTime());
    if (!$carbon->getDateAdd()) {
        $carbon->setDateAdd(new \DateTime());
    }
    $carbon->setVisible(true);

    $carbonRepository->save($carbon, true);

    return $this->json([
        'message' => 'Carbon footprint calculated successfully',
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
#[Route('/carbon/visible', name: 'app_carbon_set_all_visible_zero', methods: ['POST'])]
public function setAllCarbonVisibleToOne(CarbonRepository $carbonRepository): Response
{
    $carbonRepository->setAllCarbonVisibleToOne();
    return new Response('All carbon footprints visible values have been set to 1');
}
 // src/Controller/CarbonController.php

public function getCarbonBadge(float $carbonValue, string $productCategory): string
{
    // Définir des seuils de CO2 pour chaque catégorie de produit (en kg CO2/an)
    $categoryThresholds = [
        'LAVE_LINGE' => 200, // 200 kg CO2 par an
        'SECHE_LINGE' => 150, // 150 kg CO2 par an
        'LAVANTE_SECHANTE' => 250, // 250 kg CO2 par an
        'REFRIGERATEUR' => 300, // 300 kg CO2 par an
        'LAVE_VAISSELLE' => 150, // 150 kg CO2 par an
        'FOUR' => 100, // 100 kg CO2 par an
        'CLIMATISEUR' => 350, // 350 kg CO2 par an
        'CAVE_A_VIN' => 120, // 120 kg CO2 par an
        'CONGELATEUR' => 200, // 200 kg CO2 par an
        'HOTTE' => 30, // 30 kg CO2 par an
        'TABLE_CUISSON' => 100, // 100 kg CO2 par an
        'ASPIRATEUR' => 50, // 50 kg CO2 par an
        'CHAUFFAGE' => 400, // 400 kg CO2 par an
        'CHAUFFE_EAU' => 150, // 150 kg CO2 par an
        'CHAUDIERE' => 350, // 350 kg CO2 par an
        'TV' => 452, // 452 kg CO2 par an
    ];

    // Vérifier si la catégorie existe
    if (!isset($categoryThresholds[$productCategory])) {
        return 'Non défini';
    }

    // Comparer la valeur de l'empreinte carbone avec le seuil de la catégorie
    $threshold = $categoryThresholds[$productCategory];

    // Attribuer un badge en fonction de la comparaison
    if ($carbonValue <= $threshold * 0.5) {
        return 'Peu'; // Faible empreinte carbone
    } elseif ($carbonValue <= $threshold * 1.5) {
        return 'Moyen'; // Empreinte carbone moyenne
    } else {
        return 'Élevé'; // Haute empreinte carbone
    }
}


        
}