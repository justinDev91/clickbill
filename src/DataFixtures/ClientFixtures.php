<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ClientFixtures extends Fixture
{
  public function load(ObjectManager $manager)
  {
    $faker = Factory::create();

    for ($i = 0; $i < 20; $i++) {
      $client = new Client();
      $client->setFirstName($faker->firstName);
      $client->setLastName($faker->lastName);
      $client->setEmail($faker->unique()->email);
      $client->setPhone($faker->phoneNumber);
      $client->setAddress($faker->address);
      $client->setCreatedBy(1);
      $client->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'));
      $client->setIsDeleted(false);

      $manager->persist($client);
    }

    $manager->flush();
  }
}
