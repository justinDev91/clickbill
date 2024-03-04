<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Quote;
use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $pwd = $_ENV['SECRET_KEY'];
        $faker = Factory::create();

        // First User for all
        $admin_user = (new User())
            ->setFirstName('Admin')
            ->setLastName('Test')
            ->setEmail('admin@user.fr')
            ->setRoles(['ROLE_ADMIN']);
        $admin_user->setPassword($this->passwordHasher->hashPassword($admin_user, $pwd));
        $manager->persist($admin_user);
        $manager->flush();
        
        // First User for company
        
        $company_user = (new User())
        ->setFirstName('Company')
        ->setLastName('Test')
        ->setEmail('company@user.fr')
        ->setCreatedBy($admin_user->getId())
        ->setRoles(['ROLE_COMPANY']);
        $company_user->setPassword($this->passwordHasher->hashPassword($company_user, $pwd));
        $manager->persist($company_user);
        
        // Company

        $company = (new Company())
            ->setName('Test')
            ->setAddress('242 faubourg Saint-Antoine')
            ->setPhone('0654326494')
            ->setEmail('company@user.fr')
            ->setLogo('logo.png')
            ->setCreatedBy($company_user->getId())
            ->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'));
        $manager->persist($company);
        $manager->flush();

        //Create company's clients
        for ($i = 0; $i < 5; $i++) {
            $fakeFirstName = $faker->firstName;
            $fakeLastName = $faker->lastName;
            $fakeEmail = strtolower($fakeFirstName) . '.' . strtolower($fakeLastName) . '@demo.fr';
            $fakeEmail = iconv('UTF-8', 'ASCII//TRANSLIT', $fakeEmail);
            $fakeEmail = preg_replace('/[^a-zA-Z0-9.@]/', '', $fakeEmail);


            $client = new Client();
            $client->setFirstName($fakeFirstName)
                ->setLastName($fakeLastName)
                ->setEmail($fakeEmail)
                ->setPhone($faker->e164PhoneNumber())
                ->setAddress($faker->address)
                ->setCreatedBy(1)
                ->setCompany($company)
                ->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'))
                ->setIsDeleted(false);

            $manager->persist($client);
        }

        // Set Default Company
        $admin_user->setCompany($company);
        $manager->persist($admin_user);

        $company_user->setCompany($company);
        $manager->persist($company_user);
        $manager->flush();

        // Users

        $user = (new User())
            ->setFirstName('User')
            ->setLastName('Test')
            ->setEmail('user@user.fr')
            ->setRoles(['ROLE_USER'])
            ->setCreatedBy($admin_user->getId())
            ->setCompany($company);
        $user->setPassword($this->passwordHasher->hashPassword($user, $pwd));
        $manager->persist($user);

        $user = (new User())
            ->setFirstName('Accountant')
            ->setLastName('Test')
            ->setEmail('accountant@user.fr')
            ->setRoles(['ROLE_ACCOUNTANT'])
            ->setCreatedBy($admin_user->getId())
            ->setCompany($company);
        $user->setPassword($this->passwordHasher->hashPassword($user, $pwd));
        $manager->persist($user);
        $manager->flush();

        // Category
        $category_one = (new Category())
            ->setName('Evenement')
            ->setDescription('Les photos durant les évenements')
            ->setCompany($company)
            ->setCreatedBy($company_user->getId())
            ->setCreatedAt(new \DateTime('now'));
        $manager->persist($category_one);

        $category_two = (new Category())
            ->setName('Entreprise')
            ->setDescription('Les photos pour les entreprises')
            ->setCompany($company)
            ->setCreatedBy($company_user->getId())
            ->setCreatedAt(new \DateTime('now'));
        $manager->persist($category_two);
        $manager->flush();

        // Products
        $product = (new Product())
            ->setName('Séance Photo 1H')
            ->setDescription('Séance photo pendant 1 heure, plusieurs clichés sont réaliser..')
            ->setCategory($category_one)
            ->setCompany($company)
            ->setPrice(25.99)
            ->setCreatedBy($company_user->getId())
            ->setCreatedAt(new \DateTime('now'));
        $manager->persist($product);

        $product = (new Product())
            ->setName('Photo en entreprise')
            ->setDescription('Séance photo pour une entreprise, portrait de chaque salariées..')
            ->setCategory($category_two)
            ->setCompany($company)
            ->setPrice(179.99)
            ->setCreatedBy($company_user->getId())
            ->setCreatedAt(new \DateTime('now'));
        $manager->persist($product);
        $manager->flush();
    }
}
