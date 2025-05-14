<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProductRepository;
use App\Entity\Product;
use App\Entity\Catalog;
use App\Entity\Category;

use App\Repository\CategoryRepository;
use App\Entity\Enum\Designation;
use App\Repository\ProviderRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;


use Psr\Log\LoggerInterface;

#[Route('/Provider')]
class ProviderController extends AbstractController
{
    private LoggerInterface $logger;


    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;


    }
    

    #[Route('/email/{email}', name: 'get_provider_by_email', methods: ['GET'])]
    public function getProviderByEmail(string $email, ProviderRepository $providerRepository,UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->findOneBy(['email' => $email]);
    
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }
    
        $provider = $providerRepository->findOneBy(['User' => $user]);
    
        if (!$provider) {
            return new JsonResponse(['error' => 'provider not found for this user'], 404);
        }
    
        return new JsonResponse([
            'id' => $provider->getIdProvider(),
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            // ajoute d'autres infos si besoin
        ]);
    }
    
    #[Route('/all', name: 'all_provider', methods: ['GET'])]
    public function getAllProvider(ProviderRepository $provider_repository): JsonResponse
    {
        $provider = $provider_repository->findAllProviders();

        return $this->json($provider, 200, [], ['groups' => 'provider:read']);
        
    }

}