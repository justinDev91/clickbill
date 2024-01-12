<?php

namespace App\Controller\Front\Company;

use App\Entity\Bill;
use App\Form\BillType;
use App\Repository\BillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/bill')]
#[Security('is_granted("ROLE_COMPANY")')] # TODO: Add id for company and check if user has access to this company
class BillController extends AbstractController
{
    #[Route('/', name: 'app_bill_index', methods: ['GET', 'POST'])]
    public function index(BillRepository $billRepository, Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        // add new bill
        $bill = new Bill();
        $downPaymentBill = new Bill();
        $form = $this->createForm(BillType::class, $bill);
        $form->handleRequest($request);
        $errorBillAlreadyExist = null;
        
        if ($form->isSubmitted() && $form->isValid()) {
            $existantBills = $billRepository->findByQuoteId($form->get('quote')->getData());

            if($existantBills->isEmpty()){
                $billStatus = Bill::READY;

                if ($form->get('quote')->getData()->getDownPayment() != null) {
                    $billStatus = Bill::WAITING_FOR_DOWNPAYMENT;

                    $downPaymentBill
                        ->setQuote($form->get('quote')->getData())
                        ->setAmount($form->get('quote')->getData()->getAmount() * ($form->get('quote')->getData()->getDownPayment()/100))
                        ->setClient($form->get('quote')->getData()->getClient())
                        ->setCompany($form->get('quote')->getData()->getCompany()) # TODO: setCompany to current company
                        ->setDate($form->get('quote')->getData()->getDate())
                        ->setIsDownPayment(true)
                        ->setStatus(Bill::READY);
                        // ->setCreatedBy(1); # TODO: setCreatedBy to current user 

                    $entityManager->persist($downPaymentBill);
                    $downPaymentBill->setCreatedAt(new \DateTimeImmutable());
                    $entityManager->persist($downPaymentBill);

                    $entityManager->flush();
                }

                $bill
                    ->setAmount($form->get('quote')->getData()->getAmount() - $downPaymentBill->getAmount())
                    ->setClient($form->get('quote')->getData()->getClient())
                    ->setCompany($form->get('quote')->getData()->getCompany()) # TODO: setCreatedBy to current company
                    ->setDate($form->get('quote')->getData()->getDate())
                    ->setIsDownPayment(false)
                    ->setStatus($billStatus);
                    // ->setCreatedBy(1); # TODO: setCreatedBy to current user 
                $entityManager->persist($bill);
                $downPaymentBill->setCreatedAt(new \DateTimeImmutable());
                $entityManager->persist($bill);

                $entityManager->flush();  
            } else {
                $errorBillAlreadyExist = "Facture(s) déjà existante(s) pour ce devis";
                $session->getFlashBag()->add('error', $errorBillAlreadyExist);
            }
        }

        return $this->render('front/bill/index.html.twig', [
            'bills' => $billRepository->findAllActiveBills(),
            'form' => $form,
            'errors' => $session->getFlashBag()->get('error', []),
        ]);
    }

    #[Route('/{id}', name: 'app_bill_show', methods: ['GET'])]
    public function show(Bill $bill): Response
    {
        return $this->render('front/bill/show.html.twig', [
            'bill' => $bill,
        ]);
    }

    #[Route('/{id}', name: 'app_bill_delete', methods: ['POST'])]
    public function delete(Request $request, Bill $bill, BillRepository $billRepository, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $billAlreadySent = null;

        if ($this->isCsrfTokenValid('delete' . $bill->getId(), $request->request->get('_token'))) {
            $billsToDelete = $billRepository->findByQuoteId($bill->getQuote());
            $filteredBills = $billsToDelete->filter(fn($bill) => $bill->getStatus() != Bill::READY && $bill->getStatus() != Bill::WAITING_FOR_DOWNPAYMENT );
            $allowDelete = $filteredBills->isEmpty();

            if ($allowDelete) {
                foreach ($billsToDelete as $bill){
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
}
