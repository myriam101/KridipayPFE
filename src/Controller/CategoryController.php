<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CategoryRepository;
use App\Repository\CarbonRepository;
use Psr\Log\LoggerInterface;
use App\Entity\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/category')]

class CategoryController extends AbstractController
{
    private LoggerInterface $logger;


    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;

    }
    #[Route('/all', name: 'get_all_categories', methods: ['GET'])]
    public function getAllCategories(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findAll();

        $data = array_map(function ($category) {
            return [
                'id' => $category->getId(),
                'designation' =>$category->getDesignation()
            ];
        }, $categories);

        return new JsonResponse($data, 200);
    }
}