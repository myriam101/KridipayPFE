<?php

namespace App\Repository;

use App\Entity\Carbon;
use App\Entity\Enum\Badge;
use App\Entity\Feature;
use App\Entity\Product;
use App\Entity\Enum\Designation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Carbon>
 */
class CarbonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Carbon::class);
    }

    public function addCarbonFootprint(Product $product, float $value, bool $visible = true): Carbon
    {
        $entityManager = $this->getEntityManager();

        // Create a new Carbon entity
        $carbon = new Carbon();
        $carbon->setProduct($product);
        $carbon->setValue($value);
        $carbon->setVisible($visible);
        $carbon->setFactor(0.58);
        $carbon->setBadge(Badge::NON_DEFINI);
        $carbon->setDateAdd(new \DateTime());
        $carbon->setDateUpdate(new \DateTime());
        // Persist and flush
        $entityManager->persist($carbon);
        $entityManager->flush();

        return $carbon;
    }


    // a REVOIR IMPORTANT!!!!
    public function calculateCarbonValue(Designation $designation, Feature $feature): float
{
    switch ($designation) {
        case Designation::LAVE_LINGE: // LAVE_LINGE
            return ($feature->getConsumptionWatt() * 0.5) + 
                   ($feature->getConsumptionLiter() * 0.5) +
                   ($feature->getCycleDuration() * 0.2) + 
                   ($feature->getCapacity() * 0.1);
        case Designation::SECHE_LINGE: // SECHE_LINGE
            return ($feature->getConsumptionWatt() * 0.6) + 
                   ($feature->getCapacity() * 0.15);
        case Designation::LAVANTE_SECHANTE: // LAVANTE_SECHANTE
            return ($feature->getConsumptionWatt() * 0.55) + 
                   ($feature->getCapacity() * 0.12) + 
                   ($feature->getCycleDuration() * 0.15);
        case Designation::REFRIGERATEUR: // REFRIGERATEUR
            return ($feature->getConsumptionWatt() * 0.4) + 
                   ($feature->getVolumeRefrigeration() * 0.08);
        case Designation::LAVE_VAISSELLE: // LAVE_VAISSELLE
            return ($feature->getConsumptionWatt() * 0.45) + 
                   ($feature->getNbrCouvert() * 0.05) + 
                   ($feature->getCycleDuration() * 0.1);
        case Designation::FOUR: // FOUR
            return ($feature->getConsumptionWatt() * 0.7) + 
                   ($feature->getDimension() * 0.1);
        case Designation::CLIMATISEUR: // CLIMATISEUR
            return ($feature->getSeer() > 0) ? (500 / $feature->getSeer()) + ($feature->getScop() * 0.2) : 0;
        case Designation::CAVE_A_VIN: // CAVE_A_VIN
            return ($feature->getVolumeRefrigeration() * 0.2) + 
                   ($feature->getConsumptionWatt() * 0.3) + 
                   ($feature->getNbBottle() * 0.05);
        case Designation::CONGELATEUR: // CONGELATEUR
            return ($feature->getConsumptionWatt() * 0.5) + 
                   ($feature->getVolumeRefrigeration() * 0.08);
        case Designation::HOTTE: // HOTTE
            return ($feature->getConsumptionWatt() * 0.2) + 
                   ($feature->getNoise() * 0.05);
        case Designation::TABLE_CUISSON: // TABLE_CUISSON
            return ($feature->getConsumptionWatt() * 0.6) + 
                   ($feature->getDimension() * 0.1);
        case Designation::ASPIRATEUR: // ASPIRATEUR
            return ($feature->getConsumptionWatt() * 0.5) + 
                   ($feature->getNoise() * 0.05);
        case Designation::CHAUFFAGE: // CHAUFFAGE
            return ($feature->getPower() * 0.7); 
        case Designation::CHAUFFE_EAU: // CHAUFFE_EAU
            return ($feature->getCapacity() * 0.4) + 
                   ($feature->getConsumptionWatt() * 0.3); 
        case Designation::CHAUDIERE: // CHAUDIERE
            return ($feature->getPower() * 0.8);
        case Designation::TV: // TV
            return ($feature->getSdrConsumption() * 0.4) + 
                   ($feature->getHdrConsumption() * 0.6) + 
                   ($feature->getResolution() * 0.1) + 
                   ($feature->getDiagonal() * 0.05);
        default:
            return 0; // Valeur par défaut si la catégorie est inconnue
    }
}

public function save(Carbon $entity, bool $flush = false): void
{
    $this->getEntityManager()->persist($entity);

    if ($flush) {
        $this->getEntityManager()->flush();
    }
}
// Method to set all carbons' visible to 0/hide the display
public function setAllCarbonVisibleToZero()
{
    $qb = $this->createQueryBuilder('c')
        ->update(Carbon::class, 'c')
        ->set('c.visible', ':visible')
        ->where('c.visible = 1')
        ->setParameter('visible', 0);

    $qb->getQuery()->execute();
}
// Method to set all carbons' visible to 1
public function setAllCarbonVisibleToOne()
{
    $qb = $this->createQueryBuilder('c')
        ->update(Carbon::class, 'c')
        ->set('c.visible', ':visible')
        ->where('c.visible = 0')
        ->setParameter('visible', 1);

    $qb->getQuery()->execute();
}

 
// Méthode pour calculer l'impact carbone d'un produit en prenant l'ID du produit
public function calculateCarbonImpactByProductId(int $productId): float
{
    $product = $this->getEntityManager()->getRepository(Product::class)->find($productId);

    if (!$product) {
        return 0; 
    }
    $feature = $product->getFeature();

    if (!$feature) {
        return 0; 
    }

    $carbon = $this->findOneBy(['product' => $product]);

    $facteurEmission = 0.58;

    $consommationAnnuelle = $feature->getConsumptionWatt();


    // Calcul de l'impact carbone
    $impactEnergie = $consommationAnnuelle * $facteurEmission;

    return $impactEnergie;
}

    //    /**
    //     * @return Carbon[] Returns an array of Carbon objects
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

    //    public function findOneBySomeField($value): ?Carbon
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
