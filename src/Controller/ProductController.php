<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProductRepository;
use App\Repository\CatalogRepository;

use App\Entity\Product;
use App\Entity\Catalog;
use App\Entity\Category;
use App\Entity\Cart;
use App\Entity\Client;


use App\Entity\Feature;

use App\Repository\CategoryRepository;
use App\Entity\Enum\Designation;
use App\Entity\Enum\EnergyClass;
use App\Entity\Enum\Type;
use App\Repository\ClientRepository;
use App\Repository\CartRepository;
use App\Entity\CartContainer;
use App\Repository\ProviderRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;


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
#[Route('/provider2/{id}/add-product', name: 'add_product2_by_provider', methods: ['POST'])]
public function addProduct2ByProvider(
    Request $request,
    UserRepository $providerRepository,
    CategoryRepository $categoryRepository,
    int $id
): JsonResponse {
    $data = json_decode($request->getContent(), true);

    $provider = $providerRepository->find($id);
    if (!$provider) {
        return new JsonResponse(['error' => 'Provider not found'], 404);
    }

    $category = null;
    if (!empty($data['category_id'])) {
        $category = $categoryRepository->find($data['category_id']);
    }

    $product = new Product();
    $product->setName($data['name'] ?? '');
    $product->setDescription($data['description'] ?? '');
    $product->setShortDescription($data['short_description'] ?? '');
    $product->setReference($data['reference'] ?? '');
    $product->setBrand($data['brand'] ?? '');
    $product->setBonifpoint($data['bonifpoint'] ?? 0);
    $product->setBonifvisible($data['bonifvisible'] ?? true);
    $product->setProvider($provider);
    $product->setIdCategory($category);

    $this->entityManager->persist($product);
    $this->entityManager->flush();

    return new JsonResponse(['message' => 'Product added successfully', 'product_id' => $product->getId()], 201);
}
#[Route('/provider/{id}/add-product', name: 'add_product_by_provider', methods: ['POST'])]
public function addProductByProvider(
    Request $request,
    ProviderRepository $providerRepository,
    CategoryRepository $categoryRepository,
    CatalogRepository $catalogRepository,
    int $id
): JsonResponse {
    $data = json_decode($request->getContent(), true);

    $provider = $providerRepository->find($id);
    if (!$provider) {
        return new JsonResponse(['error' => 'Provider not found'], 404);
    }

    $category = null;
    if (!empty($data['category_id'])) {
        $category = $categoryRepository->find($data['category_id']);
    }
    // 👇 Ajout du catalog
    $catalog = null;
    if (!empty($data['id_catalog'])) {
        $catalog = $catalogRepository->find($data['id_catalog']);
        if (!$catalog) {
            return new JsonResponse(['error' => 'Catalog not found'], 404);
        }
    }

    // Création du produit
    $product = new Product();
    $product->setName($data['name'] ?? '');
    $product->setDescription($data['description'] ?? '');
    $product->setShortDescription($data['short_description'] ?? '');
    $product->setReference($data['reference'] ?? '');
    $product->setBrand($data['brand'] ?? '');
    $product->setBonifpoint($data['bonifpoint'] ?? 0);
    $product->setBonifvisible($data['bonifvisible'] ?? false);
    $product->setProvider($provider);
    $product->setIdCategory($category);
  // 👇 Lien vers le catalogue
  if ($catalog) {
    $product->setIdCatalog($catalog);
}
    $this->entityManager->persist($product);
    $this->entityManager->flush(); // pour générer l'ID

    // Ajout des features si présentes dans les données
    if (!empty($data['features'])) {
        $featuresData = $data['features'];
        $feature = new Feature();

        // Exemple d’attributs à adapter selon ton entité Feature
        $feature->setWeight($featuresData['weight'] ?? 0);
        $feature->setNoise($featuresData['noise'] ?? 0);
        $feature->setPower($featuresData['power'] ?? 0);
        $feature->setConsumptionLiter($featuresData['consumption_liter'] ?? 0);
        $feature->setConsumptionWatt($featuresData['consumption_watt'] ?? 0);
        $feature->setCapacity($featuresData['capacity'] ?? 0);
        $feature->setSeer($featuresData['seer'] ?? 0);
        $feature->setScop($featuresData['scop'] ?? 0);
        $feature->setHdrConsumption($featuresData['hdr_consumption'] ?? 0);
        $feature->setSdrConsumption($featuresData['sdr_consumption'] ?? 0);
        $feature->setDimension($featuresData['dimension'] ?? 0);
        $feature->setDiagonal($featuresData['diagonal'] ?? 0);
        $feature->setVolumeRefrigeration($featuresData['volume_refrigeration'] ?? 0);
        $feature->setVolumeCollect($featuresData['volume_collect'] ?? 0);
        $feature->setVolumeFreezer($featuresData['freezer'] ?? 0);
        $feature->setCycleDuration($featuresData['cycle_duration'] ?? 0);
        $feature->setNbrCouvert($featuresData['nbr_couvert'] ?? 0);
        $feature->setNbBottle($featuresData['nb_bottle'] ?? 0);
        $feature->setResolution($featuresData['resolution'] ?? 0);
        $feature->setDebit($featuresData['debit'] ?? 0);
        $energyClassValue = $featuresData['energy_class'] ?? null;
        if ($energyClassValue && EnergyClass::tryFrom($energyClassValue)) {
            $feature->setEnergyClass(EnergyClass::from($energyClassValue));
        }
        $typeValue = $featuresData['type'] ?? null;

        if ($typeValue && Type::tryFrom($typeValue)) {
            $feature->setType(Type::from($typeValue));
        } else {
            $feature->setType(Type::none); // Valeur par défaut
        }
        $feature->setProduct($product); // lien vers le produit

        $this->entityManager->persist($feature);
        $this->entityManager->flush();
    }

    return new JsonResponse([
        'message' => 'Product and feature added successfully',
        'product_id' => $product->getId()
    ], 201);
}


#[Route('/catalog/{catalogId}/{productId}/add', name: 'add_product_to_catalog', methods: ['POST'])]
    public function addProductToCatalog(
        int $productId,
        int $catalogId,
        EntityManagerInterface $em
    ): JsonResponse {
        $product = $em->getRepository(Product::class)->find($productId);
        $catalog = $em->getRepository(Catalog::class)->find($catalogId);

        if (!$product || !$catalog) {
            return new JsonResponse(['error' => 'Product or catalog not found'], 404);
        }

        $product->setIdCatalog($catalog);
        $em->persist($product);
        $em->flush();

        return new JsonResponse(['message' => 'Product added to catalog successfully']);
    }
    #[Route('/catalog/{catalogId}/all', name: 'get_products_by_catalog', methods: ['GET'])]
    public function getProductsByCatalog(
        int $catalogId,
        EntityManagerInterface $em
    ): JsonResponse {
        // Récupérer le catalogue à partir de son ID
        $catalog = $em->getRepository(Catalog::class)->find($catalogId);

        if (!$catalog) {
            return new JsonResponse(['error' => 'Catalog not found'], 404);
        }

        // Récupérer tous les produits associés au catalogue
        $products = $em->getRepository(Product::class)
            ->findBy(['id_catalog' => $catalog]);

        if (empty($products)) {
            return new JsonResponse(['message' => 'No products found for this catalog'], 404);
        }

        // Transformer les produits en tableau (ou en une structure JSON appropriée)
        $productData = [];
        foreach ($products as $product) {
            $productData[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'short_description'=> $product->getShortDescription(),
                'reference' => $product->getReference(),
                'brand' => $product->getBrand(),
                'bonifpoint' => $product->getBonifpoint(),
                'bonifvisible' => $product->getBonifvisible(),
                // Ajoute d'autres informations si nécessaire
            ];
        }

        return new JsonResponse($productData);
    }

    #[Route('/product/{productId}', name: 'get_product_details', methods: ['GET'])]
public function getProductDetails(
    int $productId,
    EntityManagerInterface $em
): JsonResponse {
    // Récupérer le produit à partir de son ID
    $product = $em->getRepository(Product::class)->find($productId);

    if (!$product) {
        return new JsonResponse(['error' => 'Product not found'], 404);
    }

    // Récupérer les caractéristiques du produit (Feature)
    $feature = $product->getFeature();
    $featureData = [];

    if ($feature) {
        // Vérification plus stricte : exclure les valeurs nulles ou égales à 0
        if ($feature->getWeight() && $feature->getWeight() !== 0) $featureData['weight'] = $feature->getWeight();
        if ($feature->getNoise() && $feature->getNoise() !== 0) $featureData['noise'] = $feature->getNoise();
        if ($feature->getPower() && $feature->getPower() !== 0) $featureData['power'] = $feature->getPower();
        if ($feature->getConsumptionLiter() && $feature->getConsumptionLiter() !== 0) $featureData['consumption_liter'] = $feature->getConsumptionLiter();
        if ($feature->getConsumptionWatt() && $feature->getConsumptionWatt() !== 0) $featureData['consumption_watt'] = $feature->getConsumptionWatt();
        if ($feature->getHdrConsumption() && $feature->getHdrConsumption() !== 0) $featureData['hdr_consumption'] = $feature->getHdrConsumption();
        if ($feature->getSdrConsumption() && $feature->getSdrConsumption() !== 0) $featureData['sdr_consumption'] = $feature->getSdrConsumption();
        if ($feature->getCapacity() && $feature->getCapacity() !== 0) $featureData['capacity'] = $feature->getCapacity();
        if ($feature->getDimension() && $feature->getDimension() !== 0) $featureData['dimension'] = $feature->getDimension();
        if ($feature->getVolumeRefrigeration() && $feature->getVolumeRefrigeration() !== 0) $featureData['volume_refrigeration'] = $feature->getVolumeRefrigeration();
        if ($feature->getVolumeFreezer() && $feature->getVolumeFreezer() !== 0) $featureData['volume_freezer'] = $feature->getVolumeFreezer();
        if ($feature->getVolumeCollect() && $feature->getVolumeCollect() !== 0) $featureData['volume_collect'] = $feature->getVolumeCollect();
        if ($feature->getSeer() && $feature->getSeer() !== 0) $featureData['seer'] = $feature->getSeer();
        if ($feature->getScop() && $feature->getScop() !== 0) $featureData['scop'] = $feature->getScop();
        if ($feature->getCycleDuration() && $feature->getCycleDuration() !== 0) $featureData['cycle_duration'] = $feature->getCycleDuration();
        if ($feature->getNbrCouvert() && $feature->getNbrCouvert() !== 0) $featureData['nbr_couvert'] = $feature->getNbrCouvert();
        if ($feature->getNbBottle() && $feature->getNbBottle() !== 0) $featureData['nb_bottle'] = $feature->getNbBottle();
        if ($feature->getResolution() && $feature->getResolution() !== 0) $featureData['resolution'] = $feature->getResolution();
        if ($feature->getDiagonal() && $feature->getDiagonal() !== 0) $featureData['diagonal'] = $feature->getDiagonal();
        if ($feature->getDebit() && $feature->getDebit() !== 0) $featureData['debit'] = $feature->getDebit();

        // Ajouter les enums si leur valeur n'est pas 'NONE'
        $energyClass = $feature->getEnergyClass()?->value;
        if ($energyClass && $energyClass !== 'NONE') {
            $featureData['energy_class'] = $energyClass;
        }

        $type = $feature->getType()?->value;
        if ($type && $type !== 'none') {
            $featureData['type'] = $type;
        }
    }

    // Structurer les données du produit
    $productData = [
        'id' => $product->getId(),
        'name' => $product->getName(),
        'description' => $product->getDescription(),
        'short_description' => $product->getShortDescription(),
        'reference' => $product->getReference(),
        'brand' => $product->getBrand(),
        'bonifpoint' => $product->getBonifpoint(),
        'bonifvisible' => $product->getBonifvisible(),
        'features' => !empty($featureData) ? $featureData : null,
    ];

    // Retourner la réponse JSON avec les détails du produit
    return new JsonResponse($productData);
}


    #[Route('/catalog/{catalogId}/category/{categoryId}/all', name: 'get_products_by_catalog_and_category', methods: ['GET'])]
    public function getProductsByCatalogAndCategory(
        int $catalogId,
        int $categoryId,
        EntityManagerInterface $em
    ): JsonResponse {
        $catalog = $em->getRepository(Catalog::class)->find($catalogId);
        $category = $em->getRepository(Category::class)->find($categoryId);

        if (!$catalog || !$category) {
            return new JsonResponse(['error' => 'Catalog or Category not found'], 404);
        }

        $products = $em->getRepository(Product::class)->findBy([
            'id_catalog' => $catalog,
            'id_category' => $category
        ]);

        if (empty($products)) {
            return new JsonResponse(['message' => 'No products found in this category for this catalog'], 404);
        }

        $productData = [];
        foreach ($products as $product) {
            $productData[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'reference' => $product->getReference(),
                'brand' => $product->getBrand(),
                'bonifpoint' => $product->getBonifpoint(),
                'bonifvisible' => $product->getBonifvisible(),
            ];
        }

        return new JsonResponse($productData);
    }
    #[Route('/catalog/{catalogId}/category/{categoryId}/products', name: 'get_products_by_catalog_and_designation', methods: ['GET'])]
    public function getProductsByCatalogAndDesignation(int $catalogId, int $categoryId, Request $request, ProductRepository $productRepository): JsonResponse
    {
        $designation = $request->query->get('designation');  // On récupère la designation depuis la query string (ex: /products?designation=LAVE_LINGE)
    
        // Si la designation n'est pas valide, on retourne une erreur
        try {
            $designationEnum = Designation::from($designation); // Conversion de la chaîne de la query en Enum
        } catch (\ValueError $e) {
            return $this->json(['error' => 'Invalid designation value'], 400);
        }
    
        // On récupère les produits en fonction du catalogue et de la désignation
        $products = $productRepository->findByCatalogAndDesignation($catalogId, $designationEnum);
    
        // Retourner les produits sous forme JSON
        return $this->json($products, 200);
    }
    #[Route('/cart/add', name: 'add_to_cart', methods: ['POST'])]
public function addToCart(Request $request, EntityManagerInterface $em): Response
{
    $data = json_decode($request->getContent(), true);

    if (!isset($data['client_id'], $data['product_id'])) {
        return new JsonResponse(['message' => 'Missing client_id or product_id'], 400);
    }

    $clientId = $data['client_id'];
    $productId = $data['product_id'];

    $client = $em->getRepository(Client::class)->find($clientId);
    $product = $em->getRepository(Product::class)->find($productId);

    if (!$client || !$product) {
        return new JsonResponse(['message' => 'Client or Product not found'], 404);
    }

    // Récupération ou création du panier en attente
    $cart = $em->getRepository(Cart::class)->findOneBy([
        'id_client' => $client,
        'status' => Cart::STATUS_PENDING,
    ]);

    if (!$cart) {
        $cart = new Cart();
        $cart->setIdClient($client);
        $cart->setCreatedAt(new \DateTime());
        $cart->setStatus(Cart::STATUS_PENDING);
        $em->persist($cart);
    }

    // Vérifier si le produit est déjà dans le panier
    $existingContainer = $em->getRepository(CartContainer::class)->findOneBy([
        'cart' => $cart,
        'product' => $product,
        'status' => CartContainer::STATUS_PENDING,
    ]);

    if ($existingContainer) {
        // Incrémenter la quantité si déjà présent
        $existingContainer->setQuantity($existingContainer->getQuantity() + 1);
        $em->persist($existingContainer);
    } else {
        // Ajouter le produit avec une quantité de 1
        $container = new CartContainer();
        $container->setCart($cart);
        $container->setProduct($product);
        $container->setStatus(CartContainer::STATUS_PENDING);
        $container->setQuantity(1);
        $em->persist($container);
    }

    $em->flush();

    // Comptage des produits dans le panier mis à jour
    $productCount = 0;
    foreach ($cart->getCartContainers() as $c) {
        if ($c->getStatus() === CartContainer::STATUS_PENDING) {
            $productCount += $c->getQuantity();
        }
    }

    return new JsonResponse([
        'message' => 'Product added to cart',
        'cart_product_count' => $productCount
    ], 201);
}
    
    #[Route('/cart/count/{clientId}', name: 'cart_product_count', methods: ['GET'])]
public function getCartProductCount(int $clientId, EntityManagerInterface $em): JsonResponse
{
    $client = $em->getRepository(Client::class)->find($clientId);

    if (!$client) {
        return new JsonResponse(['message' => 'Client not found'], 404);
    }

    $cart = $em->getRepository(Cart::class)->findOneBy([
        'id_client' => $client,
        'status' => Cart::STATUS_PENDING,
    ]);

    $count = 0;
    if ($cart) {
        foreach ($cart->getCartContainers() as $c) {
            if ($c->getStatus() === CartContainer::STATUS_PENDING) {
                $count += $c->getQuantity();
            }
        }
    }

    return new JsonResponse(['count' => $count]);
}
#[Route('/cart/details/{clientId}', name: 'get_cart_details', methods: ['GET'])]
public function getCartDetails(int $clientId, EntityManagerInterface $em): JsonResponse
{
    $client = $em->getRepository(Client::class)->find($clientId);

    if (!$client) {
        return new JsonResponse(['message' => 'Client not found'], 404);
    }

    $cart = $em->getRepository(Cart::class)->findOneBy([
        'id_client' => $client,
        'status' => Cart::STATUS_PENDING,
    ]);

    if (!$cart) {
        return new JsonResponse(['message' => 'No pending cart found'], 404);
    }

    $details = [];

    foreach ($cart->getCartContainers() as $container) {
        if ($container->getStatus() === CartContainer::STATUS_PENDING) {
            $product = $container->getProduct();
            $details[] = [
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'brand'=>$product->getBrand(),
                'quantity' => $container->getQuantity(),
            ];
        }
    }

    return new JsonResponse($details);
}
#[Route('/cart/{clientId}/remove/{productId}', name: 'remove_product_from_cart', methods: ['DELETE'])]
public function removeProductFromCart(int $clientId, int $productId): Response
{
    // 1. Récupérer le panier du client
    $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['id_client' => $clientId]);

    if (!$cart) {
        return new Response('Panier non trouvé pour ce client', Response::HTTP_NOT_FOUND);
    }

    // 2. Trouver le CartContainer qui correspond au produit
    $cartContainer = $this->entityManager->getRepository(CartContainer::class)->findOneBy([
        'cart' => $cart,
        'product' => $productId,
    ]);

    if (!$cartContainer) {
        return new Response('Produit non trouvé dans le panier', Response::HTTP_NOT_FOUND);
    }

    // 3. Supprimer le CartContainer
    $this->entityManager->remove($cartContainer);
    $this->entityManager->flush();

// Return a structured JSON response
return new JsonResponse([
    'status' => 'success',
    'message' => 'Produit supprimé du panier avec succès'
], Response::HTTP_OK);}


}