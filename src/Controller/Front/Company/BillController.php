<?php

namespace App\Controller\Front\Company;

use App\Entity\Bill;
use App\Form\BillFilterType;
use App\Form\BillType;
use App\Form\CustomSearchFormType;
use App\Repository\BillRepository;
use App\Repository\UserRepository;
use App\Service\PdfGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Service\InteractionService;

#[Route('/bill')]
#[Security('is_granted("ROLE_COMPANY")')] # TODO: Add id for company and check if user has access to this company
class BillController extends AbstractController
{
    #[Route('/', name: 'app_bill_index', methods: ['GET', 'POST'])]
    public function index(
        BillRepository $billRepository,
        Request $request,
        SessionInterface $session
    ): Response {
        $bill = new Bill();
        $user = $this->getUser();
        $company = $user->getCompany();

        // dd($company);
        $form = $this->createForm(BillType::class, $bill, ['company' => $company]);
        $form->handleRequest($request);

        $bills = $billRepository->findAllActiveBillsByCompany($company);

        //Status filter
        $filterForm = $this->createForm(BillFilterType::class);
        $filterForm->handleRequest($request);

        //Search Clients
        $searchForm = $this->createForm(CustomSearchFormType::class);
        $searchForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $status = $filterForm->get('status')->getData();
            if ($status) $bills = $billRepository->filterBillsByStatus($status,  $company);
        }

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchTerm = strtolower($searchForm->get('search')->getData());
            $bills = $billRepository->searchBillsByClientNameOrEmail($searchTerm,  $company);
        }

        return $this->render('front/bill/index.html.twig', [
            'bills' => $bills,
            'form' => $form,
            'searchForm' => $searchForm,
            'filterForm' => $filterForm,
            'errors' => $session->getFlashBag()->get('error', []),
        ]);
    }

    #[Route('/new', name: 'app_bill_new', methods: ['POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        BillRepository $billRepository,
        SessionInterface $session,
        InteractionService $interactionService
    ): Response {
        // add new bill
        $bill = new Bill();
        $downPaymentBill = new Bill();
        $company = $this->getUser()->getCompany();

        $form = $this->createForm(BillType::class, $bill, ['company' => $company]);
        $form->handleRequest($request);
        $errorBillAlreadyExist = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $existantBills = $billRepository->findByQuoteId($form->get('quote')->getData());
            $client = $form->get('quote')->getData()->getClient();

            if ($existantBills->isEmpty()) {
                $billStatus = Bill::READY;

                if ($form->get('quote')->getData()->getDownPayment() != null) {
                    $billStatus = Bill::WAITING_FOR_DOWNPAYMENT;

                    $downPaymentBill
                        ->setQuote($form->get('quote')->getData())
                        ->setAmount($form->get('quote')->getData()->getAmount() * ($form->get('quote')->getData()->getDownPayment() / 100))
                        ->setClient($client)
                        ->setCompany($company)
                        ->setDate($form->get('quote')->getData()->getDate())
                        ->setIsDownPayment(true)
                        ->setStatus(Bill::READY);

                    $entityManager->persist($downPaymentBill);
                    $downPaymentBill
                        ->setCreatedAt(new \DateTime())
                        ->setCreatedBy($this->getUser()->getId());
                    $entityManager->persist($downPaymentBill);

                    $interactionService->createClientInteraction(
                        $client,
                        sprintf(
                            "Une facture : %s,  avec un acompte de : %s, a été créé pour %s.",
                            $downPaymentBill->getGuid(),
                            $downPaymentBill->getAmount(),
                            $client->getDisplayName()
                        ),
                        $company,
                        "created"
                    );

                    $entityManager->flush();
                }

                $bill
                    ->setAmount($form->get('quote')->getData()->getAmount() - $downPaymentBill->getAmount())
                    ->setClient($client)
                    ->setCompany($company)
                    ->setDate($form->get('quote')->getData()->getDate())
                    ->setIsDownPayment(false)
                    ->setStatus($billStatus);
                $entityManager->persist($bill);
                $bill
                    ->setCreatedAt(new \DateTime())
                    ->setCreatedBy($this->getUser()->getId());
                $entityManager->persist($bill);

                $interactionService->createClientInteraction(
                    $client,
                    sprintf(
                        "Une facture : %s, avec monant de : %s, a été créé pour %s.",
                        $bill->getId(),
                        $bill->getAmount(),
                        $client->getDisplayName()
                    ),
                    $company,
                    "created"
                );

                $entityManager->flush();
            } else {
                $errorBillAlreadyExist = "Facture(s) déjà existante(s) pour ce devis";
                $session->getFlashBag()->add('error', $errorBillAlreadyExist);
            }
        }

        return $this->redirectToRoute('front_company_app_bill_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{guid}', name: 'app_bill_show', methods: ['GET'])]
    public function show(Bill $bill, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['id' => $bill->getCreatedBy()]);
        $quote = $bill->getQuote();
        $quoteDetails = $quote->getQuoteDetails();

        return $this->render('front/bill/show.html.twig', [
            'bill' => $bill,
            'createdBy' => ['firstname' => $user->getFirstName(), 'lastname' => $user->getLastName()],
            'bill_tva' => $quoteDetails['quoteTva'],
            'ht_amount' => number_format($quoteDetails['htAmount'], 2, ',', ' '),
            'tva_amount' => number_format($quoteDetails['tvaAmount'], 2, ',', ' '),
            'total_amount' => number_format($quoteDetails['totalAmount'], 2, ',', ' '),
        ]);
    }

    #[Route('/{guid}', name: 'app_bill_delete', methods: ['POST'])]
    public function delete(Request $request, Bill $bill, BillRepository $billRepository, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $billAlreadySent = null;

        if ($this->isCsrfTokenValid('delete' . $bill->getGuid(), $request->request->get('_token'))) {
            $billsToDelete = $billRepository->findByQuoteId($bill->getQuote());
            $filteredBills = $billsToDelete->filter(fn ($bill) => $bill->getStatus() != Bill::READY && $bill->getStatus() != Bill::WAITING_FOR_DOWNPAYMENT);
            $allowDelete = $filteredBills->isEmpty();

            if ($allowDelete) {
                foreach ($billsToDelete as $bill) {
                    //soft delete
                    $bill->setIsDeleted(true);
                    $entityManager->flush();
                }
            } else {
                $billAlreadySent = "Les factures liéés au devis #" . $bill->getQuote()->getId() . " ont déjà été émises et ne peuvent être supprimées";
                $session->getFlashBag()->add('error', $billAlreadySent);
            }
        }

        return $this->redirectToRoute('front_company_app_bill_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{guid}/pdf', name: 'app_bill_pdf', methods: ['GET'])]
    public function generatePdf(
        Bill $bill,
        PdfGeneratorService $pdfGeneratorService,
    ): Response {
        $pdfContent = $pdfGeneratorService->generateBillPdf($bill);

        return new Response(
            $pdfContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="bill.pdf"',
            ]
        );
    }
}
