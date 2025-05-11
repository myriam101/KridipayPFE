<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


#[Route('/client')]

class ClientController extends AbstractController
{
    private LoggerInterface $logger;


    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;

    }
    #[Route('/all', name: 'all_clients', methods: ['GET'])]
    public function getAllClients(ClientRepository $clientRepository): JsonResponse
    {
        $clients = $clientRepository->findAllClients();

        return $this->json($clients, 200, [], ['groups' => 'client:read']);
    }

 #[Route('/one/{id}', name: 'one_client', methods: ['GET'])]
    public function getClientById(ClientRepository $clientRepository,int $id): JsonResponse
    {
        $client = $clientRepository->findOneBy(['id' => $id]);
 if (!$client) {
            return new JsonResponse(['error' => 'Client not found'], 404);
        }
        return $this->json($client, 200, [], ['groups' => 'client:read']);

        }

    #[Route('/email/{email}', name: 'get_client_by_email', methods: ['GET'])]
    public function getClientByEmail(string $email, UserRepository $userRepository, ClientRepository $clientRepository): JsonResponse
    {
        $user = $userRepository->findOneBy(['email' => $email]);
    
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }
    
        $client = $clientRepository->findOneBy(['User' => $user]);
    
        if (!$client) {
            return new JsonResponse(['error' => 'Client not found for this user'], 404);
        }
    
        return new JsonResponse([
            'client_id' => $client->getId(),
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            // ajoute d'autres infos si besoin
        ]);
    }
    
   
}