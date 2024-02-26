<?php

namespace App\Controller\Front\Company;

use App\Entity\User;
use App\Entity\Quote;
use App\Entity\Client;
use App\Entity\Product;
use App\Form\QuoteType;
use App\Form\StatusFilterType;
use App\Form\CustomSearchFormType;
use App\Repository\QuoteRepository;
use App\Repository\UserRepository;
use App\Service\PdfGeneratorService;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


#[Route('/quote')]
#[Security('is_granted("ROLE_COMPANY")')]
class QuoteController extends AbstractController
{
    #[Route('/', name: 'app_quote_index', methods: ['GET', 'POST'])]
    public function index(Request $request, QuoteRepository $quoteRepository, SessionInterface $session): Response
    {
        $company = $this->getUser()->getCompany();

        $quotes = $quoteRepository->findAllActiveQuotes($company);

        //Search Quote
        $searchForm = $this->createForm(CustomSearchFormType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchTerm = strtolower($searchForm->get('search')->getData());
            $quotes = $quoteRepository->searchQuoteByClientNameOrEmail($searchTerm, $company);
        }

        //Status filter
        $filterForm = $this->createForm(StatusFilterType::class);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $status = $filterForm->get('status')->getData();
            if ($status) $quotes = $quoteRepository->filterQuotesByStatus($status,  $company);
        }

        return $this->render('front/quote/index.html.twig', [
            'quotes' => $quotes,
            'filterForm' => $filterForm,
            'searchForm' => $searchForm,
            'errors' => $session->getFlashBag()->get('error', []),
            'success' => $session->getFlashBag()->get('success', [])
        ]);
    }

    #[Route('/new', name: 'app_quote_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Care when using company (Admin is not supposed to have company);
        $user = $this->getUser();
        $company = $user->getCompany();
        
        $quote = new Quote();
        $form = $this->createForm(QuoteType::class, $quote, ['company' => $company]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get datas from form
            $formData = $form->getData();

            // User Company
            $quote
                ->setCompany($company)
                ->setClient($formData->getClient())
                ->setDate(new \DateTime('now'))
                ->setUpdatedBy($user->getId())
                ->setCreatedBy($user->getId());

            $productsInfoArray = [];
            $productsJSON = $request->request->get('products_datas');
            $productsDatas = json_decode($productsJSON, true);
            // Check if products JSON is empty or invalid
            if (empty($productsJSON) || !($productsDatas)) {
                $customError = 'Le devis doit contenir au moins un produit.';
                return $this->render('front/quote/new.html.twig', [
                    'quote' => $quote,
                    'form' => $form,
                    'customError' => $customError,
                ]);
            }
            $totalAmount = 0;
            foreach ($productsDatas as $product_infos) {
                // return an error in case product have 
                if ($product_infos['quantity'] < 1) {
                    $customError = 'La quantitée ne peut pas être à 0 ou négatif.';
                    return $this->render('front/quote/new.html.twig', [
                        'quote' => $quote,
                        'form' => $form,
                        'customError' => $customError,
                    ]);
                }
                $product = $entityManager->getRepository(Product::class)->findOneBy([
                    'id' => $product_infos['product_id'],
                    'company' => $company
                ]);

                if($product === null) {
                    $customError = "Un des produits n'existe pas.";
                    return $this->render('front/quote/new.html.twig', [
                        'quote' => $quote,
                        'form' => $form,
                        'customError' => $customError,
                    ]);
                }

                // Get the total amount of the product based on his price multiplied by quantity
                $productUnitPrice = $product->getPrice();
                $productQuantity = $product_infos['quantity'];
                $productAmount = $productUnitPrice * $productQuantity;
                $totalAmount += $productAmount;

                $productsInfoArray[] = [
                    'product_id' => $product_infos['product_id'],
                    'description' => $product->getName(),
                    'quantity' => $productQuantity,
                    'unit' => "", // To be added later (unit price like days/hour etc...).
                    'unitPrice' => $productUnitPrice,
                    'amount' => $productAmount
                ];
            }
            // Set all the products as JSON
            $quote->setProductsInfo($productsInfoArray);
            // Set Amount of the quote based on all products
            $quote->setAmount($totalAmount);
            // Set TVA for the quote.
            $quote->setTva($formData->getTva());

            // Set Accompte if value exist.
            $downPayment = $form->get('downPayment')->getData();
            if ($downPayment) {
                $quote->setDownPayment($downPayment);
            }

            $entityManager->persist($quote);
            $entityManager->flush();

            return $this->redirectToRoute('front_company_app_quote_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/quote/new.html.twig', [
            'quote' => $quote,
            'form' => $form,
        ]);
    }

    #[Route('/{guid}', name: 'app_quote_show', methods: ['GET'])]
    public function show(Quote $quote, UserRepository $userRepository, SessionInterface $session): Response
    {

        $company = $this->getUser()->getCompany();

        if($quote->getCompany() !== $company){
            $session->getFlashBag()->add('error', "Vous n'avez pas accès à ce devis.");
            return $this->redirectToRoute('front_company_app_quote_index', [], Response::HTTP_SEE_OTHER);
        }

        $createdByUser = $userRepository->find($quote->getCreatedBy());
        $createdBy = ['name' => $createdByUser->getDisplayName()];
        
        $quoteDetails = $quote->getQuoteDetails();

        return $this->render('front/quote/show.html.twig', [
            'quote' => $quote,
            'created_by' => $createdBy,
            'quote_tva' => $quoteDetails['quoteTva'],
            'ht_amount' => number_format($quoteDetails['htAmount'], 2, '.', ''),
            'tva_amount' => number_format($quoteDetails['tvaAmount'], 2, '.', ''),
            'total_amount' => number_format($quoteDetails['totalAmount'], 2, '.', ''),
        ]);
    }

    #[Route('/{guid}/edit', name: 'app_quote_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quote $quote, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $user = $this->getUser();
        $company = $user->getCompany();

        if ($quote->getCompany() !== $company) {
            $session->getFlashBag()->add('error', "Vous n'avez pas accès à ce devis.");
            return $this->redirectToRoute('front_company_app_quote_index', [], Response::HTTP_SEE_OTHER);
        } else if ($quote->getStatus() !== Quote::DRAFT){
            $session->getFlashBag()->add('error', "Vous ne pouvez plus modifier un devis qui a déjà été envoyé au client.");
            return $this->redirectToRoute('front_company_app_quote_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(QuoteType::class, $quote, ['company' => $company]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            // Edit Quote
            $quote
                ->setClient($formData->getClient())
                ->setDate(new \DateTime('now'))
                ->setUpdatedBy($user->getId());

            $productsInfoArray = [];
            $productsJSON = $request->request->get('products_datas');
            $productsDatas = json_decode($productsJSON, true);
            $totalAmount = 0;

            // Check if products JSON is empty or invalid
            if (empty($productsJSON) || !($productsDatas)) {
                $customError = 'Le devis doit contenir au moins un produit.';
                return $this->render('front/quote/edit.html.twig', [
                    'quote' => $quote,
                    'form' => $form,
                    'customError' => $customError,
                ]);
            }

            foreach ($productsDatas as $product_infos) {
                if ($product_infos['quantity'] < 1) {
                    // Error in case product doesn't have quantity
                    $customError = 'La quantitée ne peut pas être à 0 ou négatif.';
                    return $this->render('front/quote/edit.html.twig', [
                        'quote' => $quote,
                        'form' => $form,
                        'customError' => $customError,
                    ]);
                }

                $product = $entityManager->getRepository(Product::class)->findOneBy([
                    'id' => $product_infos['product_id'],
                    'company' => $company
                ]);

                if($product === null) {
                    $customError = "Un des produits n'existe pas.";
                    return $this->render('front/quote/new.html.twig', [
                        'quote' => $quote,
                        'form' => $form,
                        'customError' => $customError,
                    ]);
                }
                
                // Get the total amount of the product based on his price multiplied by quantity
                $productUnitPrice = $product->getPrice();
                $productQuantity = $product_infos['quantity'];
                $productAmount = $productUnitPrice * $productQuantity;
                $totalAmount += $productAmount;
                
                // Transform each product on a readable JSON for Bill.
                $productsInfoArray[] = [
                    'product_id' => $product_infos['product_id'],
                    'description' => $product->getName(),
                    'quantity' => $productQuantity,
                    'unit' => "", // To be added later.
                    'unitPrice' => $productUnitPrice,
                    'amount' => $productAmount
                ];
            }

            // Set all the products as JSON
            $quote->setProductsInfo($productsInfoArray);
            // Set Amount of the quote based on all products
            $quote->setAmount($totalAmount);
            // Set TVA for the quote.
            $quote->setTva($formData->getTva());

            // Set Accompte if value exist.
            $downPayment = $form->get('downPayment')->getData();
            if ($downPayment) {
                $quote->setDownPayment($downPayment);
            }

            $entityManager->persist($quote);
            $entityManager->flush();

            return $this->redirectToRoute('front_company_app_quote_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/quote/edit.html.twig', [
            'quote' => $quote,
            'form' => $form,
        ]);
    }

    #[Route('/{guid}', name: 'app_quote_delete', methods: ['POST'])]
    public function delete(Request $request, Quote $quote, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        if($quote->getCompany() !== $this->getUser()->getCompany()){
            $session->getFlashBag()->add('error', "Vous n'avez pas accès à ce devis.");
            return $this->redirectToRoute('front_company_app_quote_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete' . $quote->getGuid(), $request->request->get('_token'))) {
            if($quote->getStatus() !== Quote::DRAFT){
                $session->getFlashBag()->add('error', "Vous ne pouvez pas supprimer un devis qui n'est plus en brouillon.");
            } else {
                $entityManager->remove($quote);
                $entityManager->flush();
                $session->getFlashBag()->add('success', "Le devis a bien été supprimer.");
            }
        }

        return $this->redirectToRoute('front_company_app_quote_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{guid}/send-mail', name: 'app_quote_send_mail', methods: ['POST'])]
    public function send_mail(Request $request, MailService $mailService, Quote $quote, EntityManagerInterface $entityManager, PdfGeneratorService $pdfGeneratorService, UrlGeneratorInterface $urlGenerator, SessionInterface $session): Response
    {
        if($quote->getCompany() !== $this->getUser()->getCompany()){
            $session->getFlashBag()->add('error', "Vous n'avez pas accès à ce devis.");
            return $this->redirectToRoute('front_company_app_quote_index', [], Response::HTTP_SEE_OTHER);
        }

        if($quote->getStatus() === Quote::DRAFT){
            if ($this->isCsrfTokenValid('send_mail' . $quote->getGuid(), $request->request->get('_token'))) {
                try {
                    $pdfContent = $pdfGeneratorService->generateQuotePdf($quote);
    
                    // Save the quote temporaly in a temp directory in order to send it to user.
                    $tempDir = sys_get_temp_dir();
                    $pdfPath = $tempDir . '/temp_quote_' . $quote->getGuid() . '.pdf';
                    file_put_contents($pdfPath, $pdfContent);
    
                    $acceptLink = $urlGenerator->generate('front_company_app_quote_mail_answer', ['guid' => $quote->getGuid(), 'answer' => 'accept'], UrlGeneratorInterface::ABSOLUTE_URL);
                    $refuseLink = $urlGenerator->generate('front_company_app_quote_mail_answer', ['guid' => $quote->getGuid(), 'answer' => 'refuse'], UrlGeneratorInterface::ABSOLUTE_URL);

    
                    $mailService->sendTemplatedEmailWithAttachment(
                        $quote->getClient()->getEmail(),
                        'Nouveau Devis | Clickbill',
                        'emails/quote.html.twig',
                        [
                            'quote' => $quote,
                            'link_to_accept' => $acceptLink,
                            'link_to_refuse' => $refuseLink
                        ],
                        [$pdfPath]
                    );
    
                    // Delete the temporary PDF file after sending mail.
                    unlink($pdfPath);
    
                    $quote->setStatus(Quote::IN_PROGRESS);
                    $entityManager->persist($quote);
                    $entityManager->flush();
                    $session->getFlashBag()->add('success', "Le mail contenant le devis à bien été envoyé.");

                } catch (\Exception $exception) {
                    $session->getFlashBag()->add('error', "Une erreur est survenu lors de l'envoi de l'email, veuillez réessayer plus tard.");
                }
            }
        } else {
            $session->getFlashBag()->add('error', "Vous ne pouvez pas envoyer de mail pour un devis qui n'est plus en brouillon.");
        }

        return $this->redirectToRoute('front_company_app_quote_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{guid}/mail-answer/{answer}', name: 'app_quote_mail_answer', methods: ['GET'])]
    public function mail_answer(Request $request, Quote $quote, EntityManagerInterface $entityManager, $answer, SessionInterface $session): Response
    {
        $status = $quote->getStatus();
        if($status === Quote::IN_PROGRESS){
            if($answer === 'accept'){
                // When is accepted check if quote as a downPayment
                if($quote->getDownPayment()){
                    $quote->setStatus(Quote::WAITING_FOR_DOWNPAYMENT);
                } else {
                    $quote->setStatus(Quote::VALIDATED);
                }
                $entityManager->persist($quote);
                $entityManager->flush();
            } else if ($answer === 'refuse') {
                // When is refused only change status.
                $quote->setStatus(Quote::CANCELED);
                $entityManager->persist($quote);
                $entityManager->flush();
            } else {
                // Throw error message in case answer variable is not supported
                $session->getFlashBag()->add('error', 'Une erreur est survenu pour lors de la modification du devis.');
            }
        } else {
            // Send error according to the quote status.
            if ($status === Quote::WAITING_FOR_DOWNPAYMENT || $status === Quote::VALIDATED) {
                $statusError = 'Le devis a déjà été accepté.';
            } else if ($status === Quote::CANCELED) {
                $statusError = 'Le devis a déjà été refusé.';
            } else if ($status === Quote::DRAFT) {
                $statusError = "Le devis n'a pas été envoyé.";
            } else {
                $statusError = "Vous n'avez pas accès à ce devis.";
            }
            $session->getFlashBag()->add('error', $statusError);
        }
        return $this->redirectToRoute('front_company_app_quote_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{guid}/pdf', name: 'app_quote_pdf', methods: ['GET'])]
    public function generatePdf(
        Quote $quote,
        PdfGeneratorService $pdfGeneratorService
    ): Response {

        if($quote->getCompany() !== $this->getUser()->getCompany()){
            $session->getFlashBag()->add('error', "Vous n'avez pas accès à ce devis.");
            return $this->redirectToRoute('front_company_app_quote_index', [], Response::HTTP_SEE_OTHER);
        }

        $pdfContent = $pdfGeneratorService->generateQuotePdf($quote);

        return new Response(
            $pdfContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="quote.pdf"',
            ]
        );
    }
}
