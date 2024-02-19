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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

# TODO: Ajust querys and security in order to get only specific datas from user company and raise error (403) in other cases.
# TODO: Send Mail with Quote status change according to downPayment and handle answer from mail by client (care to not let user to click again on answer (by status)).
# TODO: Make Search with quoteRepository (billStatus too ?)

#[Route('/quote')]
#[Security('is_granted("ROLE_COMPANY")')]
class QuoteController extends AbstractController
{
    #[Route('/', name: 'app_quote_index', methods: ['GET', 'POST'])]
    public function index(Request $request, QuoteRepository $quoteRepository): Response
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
        ]);
    }

    #[Route('/new', name: 'app_quote_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $quote = new Quote();
        $form = $this->createForm(QuoteType::class, $quote);
        $form->handleRequest($request);
        // Créer un select de produit et selon les produits, les récupérer et créer un dictionnaire pour ensuite l'associer au json.

        if ($form->isSubmitted() && $form->isValid()) {
            // Get datas from form
            $formData = $form->getData();
            // Care when using company (Admin is not supposed to have company);
            $connectedUser = $this->getUser();
            $connectedUserCompany = $connectedUser->getCompany();

            // User Company
            $quote
                ->setCompany($connectedUserCompany)
                ->setClient($formData->getClient())
                ->setDate(new \DateTime('now'))
                ->setUpdatedBy($connectedUser->getId())
                ->setCreatedBy($connectedUser->getId());

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
                $product = $entityManager->getRepository(Product::class)->find($product_infos['product_id']);
                // Get the total amount of the product based on his price multiplied by quantity
                $productUnitPrice = $product->getPrice();
                $productQuantity = $product_infos['quantity'];
                $productAmount = $productUnitPrice * $productQuantity;
                $totalAmount += $productAmount;

                $productsInfoArray[] = [
                    'product_id' => $product_infos['product_id'],
                    'description' => $product->getName(),
                    'quantity' => $productQuantity,
                    'unit' => "", // To be added later.
                    'unitPrice' => $productUnitPrice,
                    'tva' => 20,
                    'amount' => $productAmount
                ];
            }
            // Set all the products as JSON
            $quote->setProductsInfo($productsInfoArray);
            // Set Amount of the quote based on all products
            $quote->setAmount($totalAmount);

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

    #[Route('/{id}', name: 'app_quote_show', methods: ['GET'])]
    public function show(Quote $quote, UserRepository $userRepository): Response
    {
        $createdByUser = $userRepository->find($quote->getCreatedBy());
        $createdBy = ['name' => $createdByUser->getDisplayName()];
        $htAmount = 0;
        $tvaAmount = 0;
        $totalAmount = 0;

        foreach ($quote->getProductsInfo() as $product) {
            $htAmount += $product['amount'];
            $tvaAmount += $product['amount'] / $product['tva'];
            $totalAmount += $htAmount + $tvaAmount;
        }

        return $this->render('front/quote/show.html.twig', [
            'quote' => $quote,
            'created_by' => $createdBy,
            'ht_amount' => number_format($htAmount, 2, '.', ''),
            'tva_amount' => number_format($tvaAmount, 2, '.', ''),
            'total_amount' => number_format($totalAmount, 2, '.', ''),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_quote_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Quote $quote, EntityManagerInterface $entityManager): Response
    {
        if ($quote->getStatus() !== 'brouillon') {
            throw new AccessDeniedException("Vous ne pouvez plus modifier un devis qui n'est plus en brouillon");
        }
        $form = $this->createForm(QuoteType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $connectedUser = $this->getUser();

            // Edit Quote
            $quote
                ->setClient($formData->getClient())
                ->setDate(new \DateTime('now'))
                ->setUpdatedBy($connectedUser->getId());

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
                // return an error in case product have 
                if ($product_infos['quantity'] < 1) {
                    $customError = 'La quantitée ne peut pas être à 0 ou négatif.';
                    return $this->render('front/quote/edit.html.twig', [
                        'quote' => $quote,
                        'form' => $form,
                        'customError' => $customError,
                    ]);
                }
                // Transform each product on a readable JSON for Bill.
                $product = $entityManager->getRepository(Product::class)->find($product_infos['product_id']);

                // Get the total amount of the product based on his price multiplied by quantity
                $productUnitPrice = $product->getPrice();
                $productQuantity = $product_infos['quantity'];
                $productAmount = $productUnitPrice * $productQuantity;
                $totalAmount += $productAmount;

                $productsInfoArray[] = [
                    'product_id' => $product_infos['product_id'],
                    'description' => $product->getName(),
                    'quantity' => $productQuantity,
                    'unit' => "", // To be added later.
                    'unitPrice' => $productUnitPrice,
                    'tva' => 20,
                    'amount' => $productAmount
                ];
            }

            // Set all the products as JSON
            $quote->setProductsInfo($productsInfoArray);
            // Set Amount of the quote based on all products
            $quote->setAmount($totalAmount);

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

    #[Route('/{id}', name: 'app_quote_delete', methods: ['POST'])]
    public function delete(Request $request, Quote $quote, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $quote->getId(), $request->request->get('_token'))) {
            $entityManager->remove($quote);
            $entityManager->flush();
        }

        return $this->redirectToRoute('front_company_app_quote_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/send-mail', name: 'app_quote_send_mail', methods: ['POST'])]
    public function send_mail(Request $request, MailService $mailService, Quote $quote, EntityManagerInterface $entityManager): Response
    {

        $mailService->sendTemplatedEmail(
            $formData['email'],
            'Nouveau Devis | Clickbill',
            'emails/quote.html.twig',
            []
        );
        # TODO: Check if quote is still brouillon.
        # TODO: Send mail and change status of quote.
        return $this->redirectToRoute('front_company_app_quote_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/mail-answer', name: 'app_quote_mail_answer', methods: ['GET'])]
    public function mail_answer(Request $request, Quote $quote, EntityManagerInterface $entityManager): Response
    {
        # TODO: Check if quote status already changed (not sended).
        # TODO: Send mail and change status of quote.
        return $this->redirectToRoute('front_company_app_quote_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/pdf', name: 'app_quote_pdf', methods: ['GET'])]
    public function generatePdf(
        Quote $quote,
        PdfGeneratorService $pdfGeneratorService,
    ): Response {
        $pdfContent = $pdfGeneratorService->generateQuotePdf($quote);

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
