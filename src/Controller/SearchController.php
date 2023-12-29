<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;


class SearchController extends AbstractController
{

  #[Route('/', name: 'app_search_index', methods: ['GET', 'POST'])]
  public function index(Request $request, ClientRepository $clientRepository, ValidatorInterface $validator): Response
  {
    $searchTerm = $request->query->get('search');

    $violations = $validator->validate($searchTerm, [
      new Assert\NotBlank(),
      new Assert\Type(['type' => 'string']),
      new Assert\Regex([
        'pattern' => "/^[a-zA-Z0-9\-\' ]+$/",
        'message' => "Invalid characters in address. Only alphanumeric characters, hyphens, single quotes, and spaces are allowed."
      ]),
    ]);

    if ($searchTerm) {

      if (count($violations) > 0) {
        throw new \InvalidArgumentException('Invalid search term');
      }

      $searchTerm = htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8');

      $clients = $clientRepository->searchClientByNameOrEmail($searchTerm);
    }

    return $this->render('client/index.html.twig', [
      'clients' => $clients ?? [],
    ]);
  }
}
