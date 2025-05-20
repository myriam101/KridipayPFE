<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Product;
use App\Entity\Cart;
use App\Entity\Client;
use App\Entity\CartContainer;
use App\Repository\CartRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

#[Route('/Cart')]
class CartController extends AbstractController
{
    private LoggerInterface $logger;


    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;


    }
    #[Route('/add', name: 'add_to_cart', methods: ['POST'])]
 public function addToCart(Request $request): Response
{
    $data = json_decode($request->getContent(), true);

    if (!isset($data['client_id'], $data['product_id'])) {
        return new JsonResponse(['message' => 'Missing client_id or product_id'], 400);
    }

    $clientId = $data['client_id'];
    $productId = $data['product_id'];

    $client = $this->entityManager->getRepository(Client::class)->find($clientId);
    $product = $this->entityManager->getRepository(Product::class)->find($productId);

    if (!$client || !$product) {
        return new JsonResponse(['message' => 'Client or Product not found'], 404);
    }

    $cart = $this->entityManager->getRepository(Cart::class)->findOneBy([
        'id_client' => $client,
        'status' => Cart::STATUS_PENDING,
    ]);

    if (!$cart) {
        $cart = new Cart();
        $cart->setIdClient($client);
        $cart->setCreatedAt(new \DateTime());
        $cart->setStatus(Cart::STATUS_PENDING);
        $this->entityManager->persist($cart);
    }

    $existingContainer = $this->entityManager->getRepository(CartContainer::class)->findOneBy([
        'cart' => $cart,
        'product' => $product,
        'status' => CartContainer::STATUS_PENDING,
    ]);

    if ($existingContainer) {

        $existingContainer->setQuantity($existingContainer->getQuantity() + 1);
        $this->entityManager->persist($existingContainer);
    } else {

        $container = new CartContainer();
        $container->setCart($cart);
        $container->setProduct($product);
        $container->setStatus(CartContainer::STATUS_PENDING);
        $container->setQuantity(1);
        $this->entityManager->persist($container);
    }

    $this->entityManager->flush();

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

    #[Route('/count/{clientId}', name: 'cart_product_count', methods: ['GET'])]
public function getCartProductCount(int $clientId): JsonResponse
{
    $client = $this->entityManager->getRepository(Client::class)->find($clientId);

    if (!$client) {
        return new JsonResponse(['message' => 'Client not found'], 404);
    }

    $cart = $this->entityManager->getRepository(Cart::class)->findOneBy([
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
#[Route('/details/{clientId}', name: 'get_cart_details', methods: ['GET'])]
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
                'category' => $product->getIdCategory()->getDesignation()->label()
            ];
        }
    }

    return new JsonResponse($details);
}

#[Route('/{clientId}/remove/{productId}', name: 'remove_product_from_cart', methods: ['DELETE'])]
public function removeProductFromCart(int $clientId, int $productId): Response
{
    $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['id_client' => $clientId,
        'status' => Cart::STATUS_PENDING, 
]);

    if (!$cart) {
        return new Response('Panier non trouvé pour ce client', Response::HTTP_NOT_FOUND);
    }

    $cartContainer = $this->entityManager->getRepository(CartContainer::class)->findOneBy([
        'cart' => $cart,
        'product' => $productId,
        'status' => CartContainer::STATUS_PENDING, // Added this condition
    ]);

    if (!$cartContainer) {
        return new Response('Produit non trouvé dans le panier ou produit déjà validé', Response::HTTP_NOT_FOUND);
    }

    $this->entityManager->remove($cartContainer);
    $this->entityManager->flush();

    return new JsonResponse([
        'status' => 'success',
        'message' => 'Produit supprimé du panier avec succès'
    ], Response::HTTP_OK);
}


#[Route('/client/validate-all/{clientId}', name: 'client_validate_all_cart_containers', methods: ['PATCH'])]
public function validateAllCartContainersByClient(int $clientId): JsonResponse
{
    $client = $this->entityManager->getRepository(Client::class)->find($clientId);

    if (!$client) {
        return new JsonResponse(['message' => 'Client introuvable'], 404);
    }

    $cart = $this->entityManager->getRepository(Cart::class)->findOneBy([
        'id_client' => $client,
        'status' => Cart::STATUS_PENDING,
    ]);

    if (!$cart) {
        return new JsonResponse(['message' => 'Aucun panier en attente trouvé'], 404);
    }

    $hasValidated = false;

    foreach ($cart->getCartContainers() as $container) {
        if ($container->getStatus() === CartContainer::STATUS_PENDING) {
            $container->setStatus(CartContainer::STATUS_VALIDATED);
            $hasValidated = true;
        }
    }

    if ($hasValidated) {
        $cart->setStatus(Cart::STATUS_WAITING_VALIDATION);
        $this->entityManager->flush();
        return new JsonResponse(['message' => 'Tous les produits ont été validés par le client']);
    }

    return new JsonResponse(['message' => 'Aucun produit à valider dans ce panier'], 400);
}



#[Route('/ship/{id}', name: 'admin_ship_cart', methods: ['PATCH'])]
public function shipCartContainer(int $id): JsonResponse
{
    $cartContainer = $this->entityManager->getRepository(CartContainer::class)->find($id);

    if (!$cartContainer) {
        return new JsonResponse(['message' => 'Produit non trouvé dans le panier'], 404);
    }

    if ($cartContainer->getStatus() !== CartContainer::STATUS_VALIDATED) {
        return new JsonResponse(['message' => 'Ce produit n’a pas encore été validé par le client'], 400);
    }

    $cartContainer->setStatus(CartContainer::STATUS_VALIDATED);
    $this->entityManager->flush();

    return new JsonResponse(['message' => 'Produit expédié avec succès']);
}

/**gets waiting carts by client id*/
#[Route('/client/waiting-carts/{clientId}', name: 'get_waiting_carts', methods: ['GET'])]
public function getWaitingCarts(int $clientId): JsonResponse
{
    // Récupérer le client
    $client = $this->entityManager->getRepository(Client::class)->find($clientId);

    if (!$client) {
        return new JsonResponse(['message' => 'Client non trouvé'], 404);
    }

    $carts = $this->entityManager->getRepository(Cart::class)->findBy([
        'id_client' => $client,
        'status' => Cart::STATUS_WAITING_VALIDATION
    ]);

    if (empty($carts)) {
        return new JsonResponse(['message' => 'Aucun panier en attente trouvé'], 404);
    }

    $cartDetails = [];

    foreach ($carts as $cart) {
        $products = [];
        foreach ($cart->getCartContainers() as $container) {
            $product = $container->getProduct();
            $products[] = [
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'brand' => $product->getBrand(),
                'quantity' => $container->getQuantity(),
                'status' => $container->getStatus(),
            ];
        }
        
        $cartDetails[] = [
            'cart_id' => $cart->getId(),
            'status' => $cart->getStatus(),
            'products' => $products,
            'created_at' => $cart->getCreatedAt()?->format('Y-m-d H:i'),

        ];
    }

    return new JsonResponse($cartDetails);
}


#[Route('/waiting', name: 'get_all_waiting_carts', methods: ['GET'])]
public function getAllWaitingCarts(): JsonResponse
{
    $carts = $this->entityManager->getRepository(Cart::class)->findBy([
        'status' => Cart::STATUS_WAITING_VALIDATION,
    ]);

    if (empty($carts)) {
        return new JsonResponse(['message' => 'Aucun panier en attente trouvé'], 404);
    }

    $cartDetails = [];

    foreach ($carts as $cart) {
        $client = $cart->getIdClient(); 
        $products = [];

        foreach ($cart->getCartContainers() as $container) {
            $product = $container->getProduct();
            $products[] = [
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'brand' => $product->getBrand(),
                'quantity' => $container->getQuantity(),
                'status' => $container->getStatus(),
            ];
        }

        $cartDetails[] = [
            'cart_id' => $cart->getId(),
            'client_id' => $client ? $client->getId() : null,
             'username' => $client ? $client->getUser()->getUsername() : 'Client non trouvé',
            'client_name'=>$client->getUser()->getName(),
            'client_lastname'=>$client->getUser()->getLastName(),
            'status' => $cart->getStatus(),
            'products' => $products,
            'created_at' => $cart->getCreatedAt()?->format('Y-m-d H:i'),

        ];
    }

    return new JsonResponse($cartDetails);
}
#[Route('/validated', name: 'get_all_validated_carts', methods: ['GET'])]
public function getAllValidatedCarts(): JsonResponse
{
    $carts = $this->entityManager->getRepository(Cart::class)->findBy([
        'status' => Cart::STATUS_VALIDATED,
    ]);

    if (empty($carts)) {
        return new JsonResponse(['message' => 'Aucun panier trouvé'], 404);
    }

    $cartDetails = [];

    foreach ($carts as $cart) {
        $client = $cart->getIdClient(); 
        $products = [];

        foreach ($cart->getCartContainers() as $container) {
            $product = $container->getProduct();
            $products[] = [
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'brand' => $product->getBrand(),
                'quantity' => $container->getQuantity(),
                'status' => $container->getStatus(),
            ];
        }

        $cartDetails[] = [
            'cart_id' => $cart->getId(),
            'client_id' => $client ? $client->getId() : null,
            'username' => $client ? $client->getUser()->getUsername() : 'Client non trouvé',
            'client_name'=>$client->getUser()->getName(),
            'client_lastname'=>$client->getUser()->getLastName(),
            'status' => $cart->getStatus(),
            'products' => $products,
            'created_at' => $cart->getCreatedAt()?->format('Y-m-d H:i'),
        ];
    }

    return new JsonResponse($cartDetails);
}
#[Route('/cancelled', name: 'get_all_cancelled_carts', methods: ['GET'])]
public function getAllCancelledCarts(): JsonResponse
{
    $carts = $this->entityManager->getRepository(Cart::class)->findBy([
        'status' => Cart::STATUS_CANCELLED,
    ]);

    if (empty($carts)) {
        return new JsonResponse(['message' => 'Aucun panier trouvé'], 404);
    }

    $cartDetails = [];

    foreach ($carts as $cart) {
        $client = $cart->getIdClient();
        $products = [];

        foreach ($cart->getCartContainers() as $container) {
            $product = $container->getProduct();
            $products[] = [
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'brand' => $product->getBrand(),
                'quantity' => $container->getQuantity(),
                'status' => $container->getStatus(),
            ];
        }

        $cartDetails[] = [
            'cart_id' => $cart->getId(),
            'client_id' => $client ? $client->getId() : null,
            'username' => $client ? $client->getUser()->getUsername() : 'Client non trouvé',
            'client_name'=>$client->getUser()->getName(),
            'client_lastname'=>$client->getUser()->getLastName(),
            'status' => $cart->getStatus(),
            'products' => $products,
            'created_at' => $cart->getCreatedAt()?->format('Y-m-d H:i'),

        ];
    }

    return new JsonResponse($cartDetails);
}
#[Route('/client/non-pending-carts/{clientId}', name: 'get_non_pending_carts', methods: ['GET'])]
public function getNonPendingCarts(int $clientId): JsonResponse
{
    $client = $this->entityManager->getRepository(Client::class)->find($clientId);

    if (!$client) {
        return new JsonResponse(['message' => 'Client non trouvé'], 404);
    }

    // Récupérer tous les paniers SAUF ceux avec status = 'pending'
    $qb = $this->entityManager->createQueryBuilder();
    $qb->select('c')
        ->from(Cart::class, 'c')
        ->where('c.id_client = :client')
        ->andWhere('c.status != :pending')
        ->setParameter('client', $client)
        ->setParameter('pending', Cart::STATUS_PENDING);

    $carts = $qb->getQuery()->getResult();

    if (empty($carts)) {
        return new JsonResponse(['message' => 'Aucun panier trouvé hors statut pending'], 404);
    }

    $cartDetails = [];
    foreach ($carts as $cart) {
        $products = [];
        foreach ($cart->getCartContainers() as $container) {
            $product = $container->getProduct();
            $products[] = [
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'brand' => $product->getBrand(),
                'quantity' => $container->getQuantity(),
                'status' => $container->getStatus(),
            ];
        }

        $cartDetails[] = [
            'cart_id' => $cart->getId(),
            'status' => $cart->getStatus(),
            'created_at' => $cart->getCreatedAt()?->format('Y-m-d H:i'),
            'products' => $products,

        ];
    }

    return new JsonResponse($cartDetails);
}


#[Route('/validate/{id}', name: 'validate_cart', methods: ['PUT'])]
public function validateCart(
    int $id,
    EntityManagerInterface $entityManager,
    CartRepository $cartRepository
): JsonResponse {
    $cart = $cartRepository->find($id);

    if (!$cart) {
        return new JsonResponse(['message' => 'Panier introuvable.'], JsonResponse::HTTP_NOT_FOUND);
    }

    if ($cart->getStatus() === Cart::STATUS_VALIDATED) {
        return new JsonResponse(['message' => 'Ce panier est déjà validé.'], JsonResponse::HTTP_BAD_REQUEST);
    }

    // Mettre à jour le statut du panier
    $cart->setStatus(Cart::STATUS_VALIDATED);

    // Mettre à jour les statuts des produits du panier
    foreach ($cart->getCartContainers() as $container) {
        $container->setStatus(CartContainer::STATUS_VALIDATED); // ou la valeur exacte attendue
    }

    $entityManager->flush();

    return new JsonResponse(['message' => 'Panier validé avec succès.']);
}

}