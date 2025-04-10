<?php

namespace App\Repository;

use App\Entity\BonifPoint;
use App\Entity\Client;
use App\Entity\Enum\Typepoint;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BonifPoint>
 */
class BonifPointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BonifPoint::class);
    }

    public function markBonifPointsAsUsed(int $clientId): void
    {
        $entityManager = $this->getEntityManager();
    
        $points = $this->createQueryBuilder('p')
            ->where('p.id_client = :clientId')
            ->andWhere('p.type_point = :type_point')
            ->setParameter('clientId', $clientId)
            ->setParameter('type_point', Typepoint::ACTIF)
            ->getQuery()
            ->getResult();
    
        foreach ($points as $point) {
            $point->setTypePt(Typepoint::UTILISE);
            $point->setDateUse(new \DateTime());
        }
    
        $entityManager->flush();
    }
    
 // Get all BonifPoints for a client, sorted by date of win
public function getActivePointsForClient(Client $client): array
{
    return $this->createQueryBuilder('b')
        ->where('b.id_client = :client')
        ->andWhere('b.type_point = :type')
        ->setParameter('client', $client)
        ->setParameter('type', Typepoint::ACTIF->value)
        ->orderBy('b.date_win', 'ASC')
        ->getQuery()
        ->getResult();
}
public function usePoints(Client $client, int $pointsToUse): void
{
    $em = $this->getEntityManager();

    $activePoints = $this->createQueryBuilder('b')
        ->where('b.id_client = :client')
        ->andWhere('b.type_point = :type')
        ->setParameter('client', $client)
        ->setParameter('type', Typepoint::ACTIF)
        ->orderBy('b.date_win', 'ASC')
        ->getQuery()
        ->getResult();

    $totalAvailable = 0;
    foreach ($activePoints as $bp) {
        $totalAvailable += $bp->getRemainingPts() ?? $bp->getNbrPt();
    }

    if ($totalAvailable < $pointsToUse) {
        throw new \Exception("Not enough points available to fulfill this request");
    }

    $pointsLeft = $pointsToUse;

    foreach ($activePoints as $bp) {
        $available = $bp->getRemainingPts() ?? $bp->getNbrPt();

        if ($available <= $pointsLeft) {
            $bp->setRemainingPts(0);
            $bp->setTypePoint(Typepoint::UTILISE);
            $bp->setDateUse(new \DateTime());
            $pointsLeft -= $available;
        } else {
            $bp->setRemainingPts($available - $pointsLeft);
            $bp->setDateUse(new \DateTime());
            // Type stays 'actif' because there's still points left
            $pointsLeft = 0;
        }

        if ($pointsLeft <= 0) {
            break;
        }
    }

    // Update total points in Client
    $client->setTotalBonifPts($client->getTotalBonifPts() - $pointsToUse);
    $em->flush();
}


    //    /**
    //     * @return BonifPoint[] Returns an array of BonifPoint objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?BonifPoint
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
