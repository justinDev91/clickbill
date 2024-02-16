<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\ClientInteraction;
use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;

class InteractionService
{
  private $entityManager;

  public function __construct(EntityManagerInterface $entityManager)
  {
    $this->entityManager = $entityManager;
  }

  public function createClientInteraction(
    Client $client,
    string $description,
    Company $company,
    string $action
  ): void {
    $interaction = new ClientInteraction();
    $interaction
      ->setClient($client)
      ->setDescription($description)
      ->setCompany($company)
      ->setDate(new \DateTimeImmutable())
      ->setAction($action);

    $this->entityManager->persist($interaction);
  }
}
