<?php

namespace App\Repository;

use App\Entity\PriceElectricity;
use App\Entity\Enum\Sector;
use App\Entity\Enum\TrancheElect;
use App\Entity\Enum\PeriodeUse;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PriceElectricity>
 */
class PriceElectricityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceElectricity::class);
    }

    public function existsBySectorAndTranche(Sector $sector, TrancheElect $tranche): bool
{
    return (bool) $this->createQueryBuilder('p')
        ->select('1')
        ->where('p.sector = :sector')
        ->andWhere('p.tranche_elect = :tranche')
        ->setParameter('sector', $sector)
        ->setParameter('tranche', $tranche)
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}

public function findOneByTrancheAndSector(TrancheElect $tranche, Sector $sector): ?PriceElectricity
{
    return $this->createQueryBuilder('p')
        ->where('p.tranche_elect = :tranche')
        ->andWhere('p.sector = :sector')
        ->setParameter('sector', $sector)
        ->setParameter('tranche', $tranche)
        ->getQuery()
        ->getOneOrNullResult();  // Retourne une seule entité ou null
}
public function findAllBySectorOrdered(Sector $sector): array
{
    return $this->createQueryBuilder('p')
        ->andWhere('p.sector = :sector')
        ->setParameter('sector', $sector)
        ->orderBy('p.tranche_elect', 'ASC')
        ->getQuery()
        ->getResult();
}
public function findApplicablePrice(float $consumption, Sector $sector, PeriodeUse $periode): ?PriceElectricity
{
    return $this->createQueryBuilder('p')
        ->where('p.sector = :sector')
        ->andWhere('p.periode_use = :periode')
        ->andWhere(':consumption BETWEEN p.tranche_elect.min AND p.tranche_elect.max')
        ->setParameter('consumption', $consumption)
        ->setParameter('sector', $sector)
        ->setParameter('periode', $periode)
        ->getQuery()
        ->getOneOrNullResult();
}

    //    /**
    //     * @return PriceElectricity[] Returns an array of PriceElectricity objects
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

    //    public function findOneBySomeField($value): ?PriceElectricity
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
