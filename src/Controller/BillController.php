<?php

namespace App\Controller;

use App\Entity\Bill;
use App\Form\BillType;
use App\Repository\BillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/bill')]
class BillController extends AbstractController
{
    #[Route('/', name: 'app_bill_index', methods: ['GET'])]
    public function index(BillRepository $billRepository): Response
    {
        return $this->render('bill/index.html.twig', [
            'bills' => $billRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_bill_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $bill = new Bill();
        $form = $this->createForm(BillType::class, $bill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($bill);
            $entityManager->flush();

            return $this->redirectToRoute('app_bill_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bill/new.html.twig', [
            'bill' => $bill,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bill_show', methods: ['GET'])]
    public function show(Bill $bill): Response
    {
        return $this->render('bill/show.html.twig', [
            'bill' => $bill,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_bill_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Bill $bill, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BillType::class, $bill);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_bill_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bill/edit.html.twig', [
            'bill' => $bill,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bill_delete', methods: ['POST'])]
    public function delete(Request $request, Bill $bill, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $bill->getId(), $request->request->get('_token'))) {
            $entityManager->remove($bill);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_bill_index', [], Response::HTTP_SEE_OTHER);
    }
}
