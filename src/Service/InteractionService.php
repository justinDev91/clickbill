<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\ClientInteraction;
use Doctrine\ORM\EntityManagerInterface;

class InteractionService
{
  private $entityManager;

  public function __construct(EntityManagerInterface $entityManager)
  {
    $this->entityManager = $entityManager;
  }

  public function createClientInteraction(Client $client, string $description): void
  {
    $interaction = new ClientInteraction();
    $interaction
      ->setClient($client)
      ->setDescription($description)
      ->setDate(new \DateTimeImmutable());

    $this->entityManager->persist($interaction);
  }
}
