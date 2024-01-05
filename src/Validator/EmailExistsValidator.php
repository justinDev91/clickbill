<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Doctrine\ORM\EntityManagerInterface;

class EmailExistsValidator extends ConstraintValidator
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate($email, Constraint $constraint)
    {
        /* @var App\Validator\EmailExists $constraint */

        if (null === $email || '' === $email) {
            return;
        }
        
        $existingUser = $this->entityManager
            ->getRepository(User::class)
            ->findOneByEmail(strtolower($email));
        
        if ($existingUser) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ email }}', $email)
                ->addViolation();
        }
    }
}
