<?php

namespace App\Repository;

use App\Entity\Catalog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Catalog>
 */
class CatalogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Catalog::class);
    }
/**
     * Get categories for a specific catalog
     */
    public function getCategoriesByCatalog(int $catalogId): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT DISTINCT c 
             FROM App\Entity\Category c
             JOIN App\Entity\Product p WITH p.id_category = c
             WHERE p.id_catalog = :catalogId'
        )->setParameter('catalogId', $catalogId);

        return $query->getResult();
    }

public function findByProviderId(int $providerId): array
{
    return $this->createQueryBuilder('c')
        ->andWhere('c.id_provider = :providerId')
        ->setParameter('providerId', $providerId)
        ->getQuery()
        ->getResult();
}

    //    /**
    //     * @return Catalog[] Returns an array of Catalog objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Catalog
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
