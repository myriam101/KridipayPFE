<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
         $product = new Product();
         $product->setName('product1');
         $product->setDescription('This is a static test product1.');
         $product->setReference('REF12345');
         $product->setBrand('BrandName');
         $product->setBonifpoint(100);
         $manager->persist($product);
         $manager->flush();
    }
}
