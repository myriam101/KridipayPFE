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


    }#[Route('/email/{email}', name: 'get_provider_by_email', methods: ['GET'])]
    public function getProviderByEmail(string $email, UserRepository $providerRepository): JsonResponse
    {
        $provider = $providerRepository->findOneBy(['email' => $email]);
    
        if (!$provider) {
            return new JsonResponse(['error' => 'Provider not found'], 404);
        }
    
        return new JsonResponse([
            'id' => $provider->getId(),
            'email' => $provider->getEmail(),
            // ajoute d’autres infos si besoin
        ]);
    }
    
}