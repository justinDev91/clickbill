<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        $company1 = (new Company())
            ->setName('TestForProducts')
            ->setAddress('242 faubourg Saint-Antoine1')
            ->setPhone('0645872332')
            ->setEmail('testforproducts@user.fr')
            ->setLogo('logo.png')
            ->setCreatedBy(1)
            ->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'));
        $manager->persist($company1);

        $company2 = (new Company())
            ->setName('TestForProducts2')
            ->setAddress('242 faubourg Saint-Antoine2')
            ->setPhone('0645872333')
            ->setEmail('testforproducts2@user.fr')
            ->setLogo('logo.png')
            ->setCreatedBy(1)
            ->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'));
        $manager->persist($company2);

        $category1 = new Category();
        $category1
            ->setName('Événementielle')
            ->setDescription('Mariages, Anniversaires etc.')
            ->setCreatedBy(1)
            ->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'))
            ->setCompany($company1)
            ->setIsDeleted(false);
        $manager->persist($category1);

        $category2 = new Category();
        $category2
            ->setName('Portraits')
            ->setDescription('Portraits individuels, famille etc.')
            ->setCreatedBy(1)
            ->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'))
            ->setCompany($company2)
            ->setIsDeleted(false);
        $manager->persist($category2);

        $category3 = new Category();
        $category3
            ->setName('Mode')
            ->setDescription('Shooting pour marques etc.')
            ->setCreatedBy(1)
            ->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'))
            ->setCompany($company2)
            ->setIsDeleted(false);
        $manager->persist($category3);

        $product1Category1 = new Product();
        $product1Category1
            ->setName('Mariages')
            ->setPrice(249.99)
            ->setDescription('Description mariages')
            ->setCreatedBy(1)
            ->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'))
            ->setIsDeleted(false)
            ->setCategory($category1)
            ->setCompany($company1);
        $manager->persist($product1Category1);

        $product2Category1 = new Product();
        $product2Category1
            ->setName('Anniversaires')
            ->setPrice(129.99)
            ->setDescription('Description anniversaires')
            ->setCreatedBy(1)
            ->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'))
            ->setIsDeleted(false)
            ->setCategory($category1)
            ->setCompany($company1);
        $manager->persist($product2Category1);

        $product1Category2 = new Product();
        $product1Category2
            ->setName('Portrait individuel')
            ->setPrice(99.99)
            ->setDescription('Description portrait individuel')
            ->setCreatedBy(1)
            ->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'))
            ->setIsDeleted(false)
            ->setCategory($category2)
            ->setCompany($company2);
        $manager->persist($product1Category2);

        $product2Category2 = new Product();
        $product2Category2
            ->setName('Portrait de famille')
            ->setPrice(99.99)
            ->setDescription('Description portrait de famille')
            ->setCreatedBy(1)
            ->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'))
            ->setIsDeleted(false)
            ->setCategory($category2)
            ->setCompany($company2);
        $manager->persist($product2Category2);

        $manager->flush();
    }
}
