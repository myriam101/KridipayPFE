<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Feature;
use App\Entity\Category;
use App\Entity\Enum\Designation;
use App\Entity\Enum\EnergyClass;
use App\Entity\Enum\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }


      /**
     * Trouver tous les produits d'une catégorie donnée
     */
    public function findByCategory(int $categoryId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.id_category = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->getQuery()
            ->getResult();
    }
    public function addProductWithFeatures(Product $product, Category $category)
    {
        // Assigner la catégorie au produit
        $product->setIdCategory($category);

        // Créer et assigner les features
        $this->assignFeaturesByCategory($product, $category);

        // Persister le produit
        $entityManager = $this->getEntityManager();
        $entityManager->persist($product);
        $entityManager->flush();
    }
    private function assignFeaturesByCategory(Product $product, Category $category)
    {
        // Créer un objet Feature avec des valeurs par défaut
        $feature = new Feature();
        $feature->setWeight(0);
        $feature->setNoise(0);
        $feature->setPower(0);
        $feature->setConsumptionLiter(0);
        $feature->setConsumptionWatt(0);
        $feature->setHdrConsumption(0);
        $feature->setSdrConsumption(0);
        $feature->setCapacity(0);
        $feature->setDimension(0);
        $feature->setVolumeRefrigeration(0);
        $feature->setVolumeFreezer(0);
        $feature->setVolumeCollect(0);
        $feature->setSeer(0);
        $feature->setScop(0);
        $feature->setCycleDuration(0);
        $feature->setNbrCouvert(0);
        $feature->setNbBottle(0);
        $feature->setResolution(0);
        $feature->setDiagonal(0);
        $feature->setEnergyClass(EnergyClass::NONE);  // Set a value from the enum
        $feature->setType(Type::Electrique); // Set a value from the enum
        $feature->setCondensPerform(EnergyClass::NONE);
        $feature->setLightClass(EnergyClass::NONE);
        $feature->setSpingdryClass(EnergyClass::NONE);
        $feature->setSteamClass(EnergyClass::NONE);
        $feature->setFiltreClass(EnergyClass::NONE);

        // Assigner les features au produit
        $product->setFeature($feature);

        // Persist des features
        $entityManager = $this->getEntityManager();
        $entityManager->persist($feature);
    }


    public function findByCategoryOrderedByCarbon(int $categoryId): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.category', 'c') // Joindre la table des catégories
            ->innerJoin('p.carbon', 'car') // Joindre la table des empreintes carbone
            ->andWhere('c.id = :categoryId') // Filtrer par catégorie
            ->setParameter('categoryId', $categoryId)
            ->orderBy('car.value', 'ASC') // Trier par la valeur de l'empreinte carbone (croissante)
            ->getQuery()
            ->getResult();
    }
    public function addBonusPointToProduct(int $productId, int $points): bool
{
    $product = $this->find($productId);

    if (!$product) {
        return false; // Produit non trouvé
    }

    // Ajouter les points à ceux existants
    $currentPoints = $product->getBonifpoint() ?? 0;
    $product->setBonifpoint($currentPoints + $points);

    $this->_em->persist($product);
    $this->_em->flush();

    return true;
}

 // Method to set all bonifvisible visible to 0
 public function setAllBonifToNotVisible()
 {
     $qb = $this->createQueryBuilder('c')
         ->update(Product::class, 'c')
         ->set('c.bonifvisible', ':bonifvisible')
         ->where('c.bonifvisible = 1')
         ->setParameter('bonifvisible', 0);
 
     $qb->getQuery()->execute();
 }
 
 // Method to set all bonifvisible visible to 1
 public function setAllBonifToVisible()
 {
     $qb = $this->createQueryBuilder('c')
         ->update(Product::class, 'c')
         ->set('c.bonifvisible', ':bonifvisible')
         ->where('c.bonifvisible = 0')
         ->setParameter('bonifvisible', 1);
 
     $qb->getQuery()->execute();
 }
 public function findByCatalogAndDesignation(int $catalogId, Designation $designation): ArrayCollection
 {
     // Créer la requête DQL ou QueryBuilder
     $qb = $this->createQueryBuilder('p')
         ->join('p.category', 'c') // Assurer la relation avec la catégorie
         ->join('p.catalog', 'cat') // Assurer la relation avec le catalogue
         ->where('cat.id = :catalogId')
         ->andWhere('c.designation = :designation')
         ->setParameter('catalogId', $catalogId)
         ->setParameter('designation', $designation->value);  // On passe la valeur de l'enum
 
     // Exécution de la requête
     $query = $qb->getQuery();
     $result = $query->getResult(); // On obtient un tableau de résultats
 
     // Retourner une ArrayCollection
     return new ArrayCollection($result);
 }

    
    //    /**
    //     * @return Product[] Returns an array of Product objects
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

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
