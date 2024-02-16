<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Product;
use App\Entity\Quote;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class ClientFixtures extends Fixture
{
  public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
  {
  }

  public function load(ObjectManager $manager)
  {
    $faker = Factory::create();

    //Create company
    $company = (new Company())
      ->setName('Test company')
      ->setAddress('Test adresse')
      ->setPhone('1234567')
      ->setEmail('test@gmail.com')
      ->setCreatedBy(1)
      ->setCreatedAt(new \DateTime('now'));

    //Create a company user
    $user = (new User())
      ->setFirstName('Company')
      ->setLastName('Test')
      ->setEmail('company-user@user.fr')
      ->setRoles(['ROLE_COMPANY'])
      ->setCompany($company);
    $user->setPassword($this->passwordHasher->hashPassword($user, 'test'));
    $manager->persist($user);

    //Product category
    $evenCategory = (new Category())
      ->setName('Even')
      ->setDescription('Mariages, Anniversaires etc.')
      ->setCreatedBy(1)
      ->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'))
      ->setIsDeleted(false);
    $manager->persist($evenCategory);

    for ($i = 0; $i < 20; $i++) {
      $product1 = (new Product())
        ->setName('Mariages' . ' ' .  $faker->name)
        ->setPrice($faker->randomFloat(2))
        ->setDescription($faker->text())
        ->setCreatedBy(1)
        ->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'))
        ->setIsDeleted(false)
        ->setCategory($evenCategory)
        ->setCompany($company);
      $manager->persist($product1);
    }

    //Create company's clients
    for ($i = 0; $i < 20; $i++) {
      $client = new Client();
      $client->setFirstName($faker->firstName);
      $client->setLastName($faker->lastName);
      $client->setEmail($faker->unique()->email);
      $client->setPhone($faker->phoneNumber);
      $client->setAddress($faker->address);
      $client->setCreatedBy(1);
      $client->setCompany($company);
      $client->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'));
      $client->setIsDeleted(false);

      $manager->persist($client);
    }

    $manager->flush();
  }
}
