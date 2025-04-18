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
use App\Entity\EnergyBill;
use App\Entity\Simulation;
use App\Entity\Enum\BillCategory;
use App\Entity\Enum\PeriodeUse;
use App\Entity\Enum\Designation;
use App\Repository\PriceElectricityRepository;
use App\Repository\PriceGazRepository;
use App\Repository\PriceWaterRepository;

#[Route('/EnergyBill')]

class EnergyBillController extends AbstractController
{
    private LoggerInterface $logger;


    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;

    }




}