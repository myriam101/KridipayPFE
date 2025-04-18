<?php

namespace App\Repository;

use App\Entity\PriceGaz;
use App\Entity\Enum\TrancheGaz;
use App\Entity\Enum\Sector;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PriceGaz>
 */
class PriceGazRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceGaz::class);
    }

    public function findExistingTrancheSector(TrancheGaz $tranche, Sector $sector): ?PriceGaz
{
    return $this->findOneBy([
        'tranche_gaz' => $tranche,
        'sector' => $sector
    ]);
}
public function findApplicablePrice(float $consumption, Sector $sector): ?PriceGaz
    {
        return $this->createQueryBuilder('p')
            ->where('p.sector = :sector')
            ->andWhere(':consumption BETWEEN p.tranche_gaz.min AND p.tranche_gaz.max')
            ->setParameter('consumption', $consumption)
            ->setParameter('sector', $sector)
            ->getQuery()
            ->getOneOrNullResult();
    }
    //    /**
    //     * @return PriceGaz[] Returns an array of PriceGaz objects
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

    //    public function findOneBySomeField($value): ?PriceGaz
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
