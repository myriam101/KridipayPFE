<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProductRepository;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Entity\Enum\Designation; 

use Psr\Log\LoggerInterface;

#[Route('/Product')]
class ProductController extends AbstractController
{
    private LoggerInterface $logger;


    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;


    }


    #[Route('/add', name: 'product_add', methods: ['POST'])]
public function addProduct(Request $request, CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
{
    //$this->logger->info('Received request data: ' . json_encode($request->request->all()));

    $name = $request->request->get('name');
    $description = $request->request->get('description');
    $designationInt = (int) $request->request->get('designation');
    $shortDescription = $request->request->get('short_description');
    $reference = $request->request->get('reference');
    $brand = $request->request->get('brand');
    $bonifpoint = (int) $request->request->get('bonifpoint');

    // Convert designation integer to enum
    try {
        $designation = Designation::from($designationInt);  
    } catch (\ValueError $e) {
        return new Response('Invalid designation value', 400); 
    }

    $category = $categoryRepository->findByDesignation($designation);

    if (!$category) {
        return new Response('Category not found for the given designation', 404);
    }

    $product = new Product();
    $product->setName($name);
    $product->setDescription($description);
    $product->setShortDescription($shortDescription);
    $product->setReference($reference);
    $product->setBrand($brand);
    $product->setBonifpoint($bonifpoint);
    $product->setIdCategory($category);

    $productRepository->addProductWithFeatures($product, $category);

    return new Response('Product added successfully');

}

//** update or add bonus point to a specified product */
#[Route('/update-bonif', name: 'product_update_bonif', methods: ['PUT'])]
public function updateBonif(Request $request, ProductRepository $productRepository, EntityManagerInterface $em): Response
{
    $productId = $request->headers->get('product_id');
    $data = json_decode($request->getContent(), true);
    $points = $data['bonifpoint'] ?? null;

    if (!$productId || $points === null) {
        return new Response('Missing product_id or points', 400);
    }

    $product = $productRepository->find($productId);

    if (!$product) {
        return new Response('Product not found', 404);
    }

    $product->setBonifpoint((int) $points);
    $em->persist($product);
    $em->flush();

    return new Response("Bonification points updated to $points for product ID $productId", 200);
}

/** bonif not visible on products */
#[Route('/bonifnotvisible', name: 'app_product_bonifnotvisible', methods: ['POST'])]
public function setAllBonifVisibleToZero(ProductRepository $productRepository): Response
{
    $productRepository->setAllBonifToNotVisible();
    return new Response('bonif points not visible anymore');
}
/** bonif visible on products */

#[Route('/bonifvisible', name: 'app_product_bonifvisible', methods: ['POST'])]
public function setAllBonifVisibleToOne(ProductRepository $productRepository): Response
{
    $productRepository->setAllBonifToVisible();
    return new Response('bonif points visible');
}

/** display bonif point only if "bonifvisible" is set to 1 */
#[Route('/pointsdisplay', name: 'get_points', methods: ['GET'])]
public function getVisibleBonifPoints(ProductRepository $productRepository): Response
{
    $products = $productRepository->findBy(['bonifvisible' => true]);

    $data = array_map(function ($product) {
        return [
            'product_id' => $product->getId(),
            'name' => $product->getName(),
            'bonifpoint' => $product->getBonifpoint()
        ];
    }, $products);

    return new Response(json_encode($data), 200, ['Content-Type' => 'application/json']);
}

}