<?php

namespace App\DataFixtures;

use App\Entity\EnergyBill;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;
use App\Entity\Enum\Type;
use App\Entity\Enum\EnergyClass;
use App\Entity\Enum\Designation;
use App\Entity\Feature;
use App\Entity\Category;


class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
         $product = new Product();
         $product->setName('product3');
         $product->setDescription('This is a static test product3.');
         $product->setReference('REF12345');
         $product->setBrand('BrandName');
         $product->setBonifpoint(100);
         // Creating and associating Feature
        $feature = new Feature();
        $feature->setWeight(15);
        $feature->setPower(200);
        $feature->setNoise(50);
        $feature->setEnergyClass(EnergyClass::A);  // Set a value from the enum
        $feature->setType(Type::Electrique); // Set a value from the enum
        $feature->setConsumptionLiter(10);
        $feature->setConsumptionWatt(20);
        $feature->setHdrConsumption(0);
        $feature->setSdrConsumption(0);
        $feature->setCapacity(10);
        $feature->setDiagonal(0);
        $feature->setDimension(0);
        $feature->setVolumeRefrigeration(0);
        $feature->setVolumeFreezer(0);
        $feature->setVolumeCollect(0);
        $feature->setSeer(0);
        $feature->setScop(0);
        $duration = '300';
        $feature->setCycleDuration($duration);
        $feature->setNbrCouvert(0);
        $feature->setNbBottle(0);
        $feature->setResolution(0);
        $feature->setCondensPerform(EnergyClass::A);
        $feature->setSpingdryClass(EnergyClass::B);
        $feature->setSteamClass(EnergyClass::A);
        $feature->setLightClass(EnergyClass::A);
        $feature->setFiltreClass(EnergyClass::B);


        $category = new Category();

        $category->setName('lave linge')
         ->setDescription('Devices and gadgets')
         ->setDesignation(Designation::LAVE_LINGE);

        $manager->persist($category);



        // Set other Feature fields...

        // Associate the feature with the product
        $product->setFeature($feature);
        $product->setIdCategory($category);


        // Persist both entities (product and feature)
         $manager->persist($feature);
         $manager->persist($product);
         $manager->flush();
    }
}
