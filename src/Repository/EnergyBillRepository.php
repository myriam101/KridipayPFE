<?php

namespace App\Repository;

use App\Entity\EnergyBill;
use App\Entity\Simulation;
use App\Entity\PriceWater;
use App\Entity\Enum\TrancheEau;
use App\Entity\Enum\BillCategory;



use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EnergyBill>
 */
class EnergyBillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EnergyBill::class);
    }
    public function calculateWaterBill(Simulation $simulation, BillCategory $billCategory): float
    {
        $feature = $simulation->getProduct()->getFeature();
        if (!$feature || !$feature->getConsumptionLiter()) {
            return 0.0;
        }

        $dailyUsage = $simulation->getNbrUse();
        $durationHours = $simulation->getDurationUse();

        $multiplier = match ($billCategory) {
            BillCategory::MOIS => 30,
            BillCategory::TRIMESTRE => 90,
            BillCategory::ANS => 365,
        };

        // consommation totale = consommation par cycle * nb d’utilisation par jour * nb de jours
        $totalConsumption = $feature->getConsumptionLiter() * $dailyUsage * $multiplier;

        // chercher la tranche correspondante
        $tranche = $this->getTrancheEauFromConsumption($totalConsumption);

        $priceWater = $this->_em->getRepository(PriceWater::class)->findOneBy([
            'tranche_eau' => $tranche
        ]);

        if (!$priceWater) {
            return 0.0;
        }

        return $totalConsumption * $priceWater->getPrice();
    }

    private function getTrancheEauFromConsumption(float $consumption): TrancheEau
    {
        return match (true) {
            $consumption <= 20 => TrancheEau::ZERO_TWENTY,
            $consumption <= 40 => TrancheEau::TWENTY_ONE_FORTY,
            $consumption <= 70 => TrancheEau::FORTY_ONE_SEVENTY,
            $consumption <= 100 => TrancheEau::SEVENTY_ONE_HUNDRED,
            $consumption <= 150 => TrancheEau::HUNDRED_ONE_HUNDRED_FIFTY,
            default => TrancheEau::HUNDRED_FIFTY_PLUS,
        };
    }

    //    /**
    //     * @return EnergyBill[] Returns an array of EnergyBill objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?EnergyBill
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
