<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $pwd = 'test';

        $user = (new User())
            ->setFirstName('Company')
            ->setLastName('Test')
            ->setEmail('company@user.fr')
            ->setRoles(['ROLE_COMPANY']);;
        $user->setPassword($this->passwordHasher->hashPassword($user, $pwd));
        $manager->persist($user);

        $company = (new Company())
            ->setName('Test')
            ->setAddress('242 faubourg Saint-Antoine')
            ->setPhone('0654326494')
            ->setEmail('company@user.fr')
            ->setLogo('logo.png')
            ->setCreatedBy($user->getId())
            ->setCreatedAt(new \DateTime('now'));
        $manager->persist($company);
        $manager->flush();

        $user->setCompany($company);
        $manager->persist($user);
        $manager->flush();

        $user = (new User())
            ->setFirstName('User')
            ->setLastName('Test')
            ->setEmail('user@user.fr')
            ->setRoles(['ROLE_USER'])
            ->setCompany($company);;
        $user->setPassword($this->passwordHasher->hashPassword($user, $pwd));
        $manager->persist($user);

        $user = (new User())
            ->setFirstName('Accountant')
            ->setLastName('Test')
            ->setEmail('accountant@user.fr')
            ->setRoles(['ROLE_ACCOUNTANT'])
            ->setCompany($company);;
        $user->setPassword($this->passwordHasher->hashPassword($user, $pwd));
        $manager->persist($user);

        $user = (new User())
            ->setFirstName('Admin')
            ->setLastName('Test')
            ->setEmail('admin@user.fr')
            ->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $pwd));
        $manager->persist($user);
        $manager->flush();

        $manager->flush();
    }
}
