<?php

namespace App\Service;

use App\Entity\Bill;
use App\Entity\Quote;
use App\Repository\UserRepository;
use Dompdf\Dompdf;
use Twig\Environment;

class PdfGeneratorService
{
  private Environment $twig;
  private $userRepository;


  public function __construct(Environment $twig, UserRepository $userRepository)
  {
    $this->twig = $twig;
    $this->userRepository = $userRepository;
  }

  public function generateBillPdf(Bill $bill): string
  {
    $dompdf = new Dompdf();

    $user = $this->userRepository->findOneBy(['id' => $bill->getCreatedBy()]);

    $creatorDisplayName = $user->getDisplayName();

    $billTotalAmount = 0;

    foreach ($bill->getQuote()->getProductsInfo() as $product) {
      $billTotalAmount += $product["amount"];
    }

    $html = $this->twig->render('front/bill/pdf_template.html.twig', [
      'bill' => $bill,
      'creatorDisplayName' => $creatorDisplayName,
      'billTotalAmount' => $billTotalAmount,
    ]);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return $dompdf->output();
  }

  public function generateQuotePdf(Quote $quote): string
  {
    $dompdf = new Dompdf();

    $user = $this->userRepository->findOneBy(['id' => $quote->getCreatedBy()]);

    $creatorDisplayName = $user->getDisplayName();

    $quoteTotalAmount = 0;

    foreach ($quote->getProductsInfo() as $product) {
      $quoteTotalAmount += $product["amount"];
    }

    $quoteDetails = $quote->getQuoteDetails();

    $html = $this->twig->render('front/quote/pdf_template.html.twig', [
      'quote' => $quote,
      'creatorDisplayName' => $creatorDisplayName,
      'quoteTotalAmount' => $quoteTotalAmount,
      'ht_amount' => $quoteDetails['htAmount'],
      'tva_amount' => $quoteDetails['tvaAmount'],
      'quote_tva' => $quoteDetails['quoteTva'],
      'total_amount' => $quoteDetails['totalAmount']
    ]);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return $dompdf->output();
  }
}
