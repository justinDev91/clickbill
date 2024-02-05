<?php

namespace App\Controller\Front\Company;

use App\Entity\Client;
use App\Form\ClientType;
use App\Form\CustomSearchFormType;
use App\Form\StatusFilterType;
use App\Repository\ClientRepository;
use App\Service\InteractionService;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/client')]
#[Security('is_granted("ROLE_COMPANY")')] # TODO: Add id for company and check if user has access to this company
class ClientController extends AbstractController
{

    #[Route('/', name: 'app_client_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        ClientRepository $clientRepository,
        PaginationService $paginationService
    ): Response {
        $clients = $clientRepository->findAllActiveClients();

        //Status filter
        $statusFilterForm = $this->createForm(StatusFilterType::class);
        $statusFilterForm->handleRequest($request);

        //Search Clients
        $searchForm = $this->createForm(CustomSearchFormType::class);
        $searchForm->handleRequest($request);

        if ($statusFilterForm->isSubmitted() && $statusFilterForm->isValid()) {
            $status = $statusFilterForm->get('status')->getData();
            $clients = $clientRepository->filterClientsByStatus($status);
        }

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchTerm = strtolower($searchForm->get('search')->getData());
            $clients = $clientRepository->searchClientByNameOrEmail($searchTerm);
        }

        $pagination = $paginationService->paginate(
            $clients,
            $request
        );

        return $this->render('front/client/index.html.twig', [
            'clients' => $clients,
            'statusFilterForm' => $statusFilterForm,
            'searchForm' => $searchForm,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        InteractionService $interactionService
    ): Response {
        $client = new Client();
        $connectedUserId = $this->getUser()->getId();

        $client
            ->setIsDeleted(false)
            ->setCreatedBy($connectedUserId)
            ->setUpdatedBy($connectedUserId);

        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($client);

            $interactionService->createClientInteraction(
                $client,
                sprintf("Client %s is created", $client->getFirstName(), $client->getLastName())
            );

            $entityManager->flush();

            return $this->redirectToRoute('front_company_app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'app_client_show', methods: ['GET'])]
    public function show(Client $client): Response
    {
        return $this->render('front/client/show.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_client_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Client $client,
        EntityManagerInterface $entityManager,
        InteractionService $interactionService
    ): Response {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $interactionService->createClientInteraction(
                $client,
                sprintf("Client %s is edited", $client->getFirstName(), $client->getLastName())
            );

            $entityManager->flush();

            return $this->redirectToRoute('front_company_app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'app_client_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Client $client,
        EntityManagerInterface $entityManager,
        InteractionService $interactionService
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $client->getSlug(), $request->request->get('_token'))) {

            // Perform soft delete
            $client->setIsDeleted(true);

            $interactionService->createClientInteraction(
                $client,
                sprintf("Client %s is edited", $client->getFirstName(), $client->getLastName())
            );

            $entityManager->flush();
        }

        return $this->redirectToRoute('front_company_app_client_index', [], Response::HTTP_SEE_OTHER);
    }
}