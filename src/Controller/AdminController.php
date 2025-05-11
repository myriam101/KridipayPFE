<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\BonifPointRepository;
use App\Entity\BonifPoint;
use App\Entity\Client;
use App\Entity\Enum\Typepoint;
use App\Repository\AdminRepository;
use App\Repository\ClientRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

use Psr\Log\LoggerInterface;

#[Route('/Admin')]
class AdminController extends AbstractController
{
    private LoggerInterface $logger;


    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;

    }
 #[Route('/email/{email}', name: 'get_admin_by_email', methods: ['GET'])]
    public function getClientByEmail(string $email, UserRepository $userRepository, AdminRepository $adminRepository): JsonResponse
    {
        $user = $userRepository->findOneBy(['email' => $email]);
    
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }
    
        $admin = $adminRepository->findOneBy(['User' => $user]);
    
        if (!$admin) {
            return new JsonResponse(['error' => 'admin not found for this user'], 404);
        }
    
        return new JsonResponse([
            'id' => $admin->getId(),
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            // ajoute d'autres infos si besoin
        ]);
    }
    

}