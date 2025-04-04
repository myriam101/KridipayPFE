<?php

namespace App\Repository;

use Psr\Log\LoggerInterface;

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

    //** pour l'attribution et recalcul des badge lors de l'ajout d'une empreinte carbone a un produit existant */
    public function addCarbonFootprint2(Product $product, float $value, bool $visible = true,Badge $badge): Carbon
    {
        $entityManager = $this->getEntityManager();

        // Create a new Carbon entity
        $carbon = new Carbon();
        $carbon->setProduct($product);
        $carbon->setValue($value);
        $carbon->setVisible($visible);
        $carbon->setFactor(0.58);
        $carbon->setBadge($badge);
        $carbon->setDateAdd(new \DateTime());
        $carbon->setDateUpdate(new \DateTime());
        // Persist and flush
        $entityManager->persist($carbon);
        $entityManager->flush();

        return $carbon;
    }
    /** ajout empreinte normal */
    public function addCarbonFootprint(Product $product, float $value, bool $visible = true): Carbon
    {
        $entityManager = $this->getEntityManager();

        // Create a new Carbon entity
        $carbon = new Carbon();
        $carbon->setProduct($product);
        $carbon->setValue($value);
        $carbon->setVisible($visible);
        $carbon->setFactor(0.58);
        $carbon->setBadge(Badge :: NON_DEFINI);
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
//** Method to set all carbons' visible to 0-hide the display */ 
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
private function calculateBadge(float $carbonValue, array $carbonValues): Badge
{
    sort($carbonValues);
    $count = count($carbonValues);

    if ($count < 2) {
        return Badge::NON_DEFINI; // Impossible de comparer si un seul produit
    }

    $q1 = $carbonValues[(int) floor(($count - 1) * 0.25)];
    $q3 = $carbonValues[(int) floor(($count - 1) * 0.75)];

    if ($carbonValue <= $q1) {
        return Badge::PEU;
    } elseif ($carbonValue > $q1 && $carbonValue <= $q3) {
        return Badge::MOYEN;
    } else {
        return Badge::ELEVE;
    }
}

public function assignBadgeToProduct(Product $product, float $carbonValue): void
{
    $categoryId = $product->getIdCategory(); // Assurez-vous que getCategory() retourne bien une entité

    // Récupérer toutes les empreintes carbone de la catégorie
    $productsInCategory = $this->createQueryBuilder('c')
        ->innerJoin('c.product', 'p')
        ->where('p.id_category = :categoryId')
        ->setParameter('categoryId', $categoryId)
        ->getQuery()
        ->getResult();

    // Récupérer toutes les valeurs des empreintes carbone
    $carbonValues = array_map(fn($c) => $c->getValue(), $productsInCategory);

    if (empty($carbonValues)) {
        // Si aucun produit dans la catégorie, on met NON_DEFINI
        $badge = Badge::NON_DEFINI;
    } else {
        $carbonValues[] = $carbonValue; // Inclure la nouvelle empreinte
        $badge = $this->calculateBadge($carbonValue, $carbonValues);
    }
    $this->addCarbonFootprint2($product,$carbonValue, true,$badge);

}

public function recalculateBadgesAfterDeletion(int $categoryId): void
{
    $productsInCategory = $this->createQueryBuilder('c')
        ->innerJoin('c.product', 'p')
        ->where('p.id_category = :categoryId')
        ->setParameter('categoryId', $categoryId)
        ->getQuery()
        ->getResult();

    if (count($productsInCategory) === 0) {
        return; // Aucun produit restant, pas de badge à attribuer
    }

    // Recalcul des quartiles
    $carbonValues = array_map(fn($c) => $c->getValue(), $productsInCategory);
    sort($carbonValues);
    $count = count($carbonValues);
    $q1 = $carbonValues[intval($count * 0.25)];
    $q3 = $carbonValues[intval($count * 0.75)];

    // Réattribution des badges
    foreach ($productsInCategory as $carbon) {
        $carbon->setBadge($this->calculateBadge($carbon->getValue(), $carbonValues));
        $this->getEntityManager()->persist($carbon);
    }

    $this->getEntityManager()->flush();
}
//
public function assignBadgeToProductUpdate(Product $product, float $carbonValue): void
{
    $categoryId = $product->getIdCategory();

    // Retrieve all carbon footprints in the category
    $productsInCategory = $this->createQueryBuilder('c')
        ->innerJoin('c.product', 'p')
        ->where('p.id_category = :categoryId')
        ->setParameter('categoryId', $categoryId)
        ->getQuery()
        ->getResult();

    // Get all carbon values
    $carbonValues = array_map(fn($c) => $c->getValue(), $productsInCategory);
    $carbonValues[] = $carbonValue; // Include the new carbon value

    // Determine the badge
    $badge = $this->calculateBadge($carbonValue, $carbonValues);

    // Find the existing Carbon entry for the product
    $carbon = $this->findOneBy(['product' => $product]);

    if (!$carbon) {
        // If no existing entry, create a new one
        $carbon = new Carbon();
        $carbon->setProduct($product);
    }

    // Update the carbon attributes
    $carbon->setValue($carbonValue);
    $carbon->setBadge($badge);

    $this->getEntityManager()->persist($carbon);
    $this->getEntityManager()->flush();
}

/**recalcul et attribue les badges au carbon deja existants***/
public function recalculateAllBadges(): void
{
    $entityManager = $this->getEntityManager();

    // Log the start of the process
    //$logger->info('Started retrieving products and carbon values for badge recalculation.');

    // Retrieve all carbon footprints along with their respective products
    $productsByCategory = $this->createQueryBuilder('c')
        ->innerJoin('c.product', 'p') // Join the Carbon entity with the Product entity
        ->where('p.id_category IS NOT NULL')  // Ensure only products with category are considered
        ->getQuery()
        ->getResult();

    // Log how many products were found
   // $logger->info('Found ' . count($productsByCategory) . ' products to recalculate badges for.');

    // Group products by category
    $categoryProducts = [];
    foreach ($productsByCategory as $carbon) {
        $product = $carbon->getProduct(); // Get the related product
        $category = $product->getIdCategory(); // Get the related category object
        $categoryId = $category ? $category->getId() : null; // Get category ID
        $carbonValue = $carbon->getValue(); // Get the carbon value of the product

        // Log each product's category and carbon value
       // $logger->info('Product ID ' . $product->getId() . ' belongs to category ID ' . $categoryId . ' with carbon value ' . $carbonValue);

        $categoryProducts[$categoryId][] = [
            'carbonId' => $carbon->getId(),
            'value' => $carbonValue,
        ];
    }

    // Recalculate and update badges for each category
    foreach ($categoryProducts as $categoryId => $carbonValues) {
        // Sort the carbon values and calculate the badge
        usort($carbonValues, fn($a, $b) => $a['value'] <=> $b['value']); // Sort by carbon value
        $values = array_column($carbonValues, 'value');
        
        // Log the category and the carbon values sorted
     //   $logger->info('Category ' . $categoryId . ' has carbon values: ' . implode(', ', $values));

        // Recalculate badge for each product in this category
        foreach ($carbonValues as $productData) {
            $carbon = $this->find($productData['carbonId']);
            $newBadge = $this->calculateBadge($productData['value'], $values); // Use the method to calculate the badge

            // Log the old badge and the new badge
          //  $logger->info('Product ID ' . $carbon->getProduct()->getId() . ' - Old Badge: ' . $carbon->getBadge()->name . ' -> New Badge: ' . $newBadge->name);

            $carbon->setBadge($newBadge); // Set the new badge

            // Persist the updated carbon footprint
            $entityManager->persist($carbon);
        }
    }

    // Save the changes to the database
    $entityManager->flush();

    // Log the end of the process
   // $logger->info('Carbon badges recalculated and saved successfully.');
}
public function removeByProductId(int $productId, bool $flush = false): void
{
    // Find the Carbon entity associated with the productId
    $carbon = $this->findOneBy(['product' => $productId]);

    if ($carbon) {
        // Remove the found Carbon entity
        $this->getEntityManager()->remove($carbon);

        if ($flush) {
            // Flush changes to the database
            $this->getEntityManager()->flush();
        }
    } else {
        // Optionally, log an error or handle case where no carbon is found for the productId
        throw new \Exception('Carbon footprint not found for the given productId.');
    }
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
