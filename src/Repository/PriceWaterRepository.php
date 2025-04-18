<?php

namespace App\Repository;

use App\Entity\PriceWater;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Enum\TrancheEau;
use App\Entity\Enum\BillCategory;
/**
 * @extends ServiceEntityRepository<PriceWater>
 */
class PriceWaterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceWater::class);
    }
    public function existsByTranche(TrancheEau $tranche): bool
{
    return (bool) $this->createQueryBuilder('pw')
        ->select('1')
        ->where('pw.tranche_eau = :tranche')
        ->setParameter('tranche', $tranche)
        ->getQuery()
        ->getOneOrNullResult();
}
public function findApplicablePrice(float $consumption): ?PriceWater
    {
        return $this->createQueryBuilder('p')
            ->where(':consumption BETWEEN p.tranche_eau.min AND p.tranche_eau.max')
            ->setParameter('consumption', $consumption)
            ->getQuery()
            ->getOneOrNullResult();
    }



    //    /**
    //     * @return PriceWater[] Returns an array of PriceWater objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PriceWater
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
