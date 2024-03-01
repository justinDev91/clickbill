<?php

namespace App\Controller\Front\Accountant;

use App\Entity\Quote;
use App\Repository\BillRepository;
use App\Repository\ClientRepository;
use App\Repository\QuoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class AccountantController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        ChartBuilderInterface $chartBuilder,
        ClientRepository $clientRepository,
        BillRepository $billRepository,
        QuoteRepository $quoteRepository,
    ): Response {

        $company = $this->getUser()->getCompany();

        $data = [
            // Clients
            'clients' => $clientRepository->findActiveClientsByCompany($company),
            'totalClients' => $clientRepository->getTotalClientCountForCompany($company),
            'totalClientsWithQuoteInProgress' => $clientRepository->getTotalClientCountWithQuoteOnProcess($company),
            'totalClientsWithQuoteValidated' => $clientRepository->getTotalClientCountWithQuoteValided($company),
            'totalClientsWithBillPaid' => $clientRepository->getTotalClientCountWithBillPaid($company),

            // Devis
            'totalQuotes' => $quoteRepository->getTotalQuoteCountForCompany($company),
            'totalValidedQuotes' => $quoteRepository->getTotalQuoteByStatus($company, Quote::VALIDATED),
            'totalInprocressQuotes' => $quoteRepository->getTotalQuoteByStatus($company, Quote::IN_PROGRESS),

            // Bills
            'totalBills' => $billRepository->getTotalBillCountByStatusForCompany($company),
            'totalUnpaidBills' => $billRepository->getTotalBillCountByStatusForCompany($company, 'unpaid'),
            'totalPaidBills' => $billRepository->getTotalBillCountByStatusForCompany($company, 'paid'),
        ];

        $barChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $lineChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $doughnutChart = $chartBuilder->createChart(Chart::TYPE_BAR);

        $doughnutChart->setData([
            'labels' => ['Le total des devis', 'Le total des factures payés', 'Le total des factures impayés'],
            'datasets' => [
                [
                    'label' => 'Le nombre total des devis validés par mois',
                    'backgroundColor' => ['rgb(255, 99, 132)', 'rgb(54, 162, 235)', 'rgb(246, 195, 30)'],
                    'data' => [$data['totalQuotes'], $data['totalPaidBills'], $data['totalUnpaidBills']],
                ],
            ],
        ]);


        $lineChart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'Le total des devis validés par mois',
                    'backgroundColor' => 'rgb(246, 195, 30)',
                    'borderColor' => 'rgb(75, 192, 192)',
                    'data' => [0, 10, 5, 2, 20, 30, 45],
                ],
            ],
        ]);


        $barChart->setData([
            'labels' => ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            'datasets' => [
                [
                    'label' => 'Le total des clients par mois',
                    'backgroundColor' => 'rgb(246, 195, 30)',
                    'data' => [0, 10, 5, 2, 20, 30, 45],
                ],
            ],
        ]);

        $barChart->setOptions([
            'scales' => [
                'x' => [
                    'grid' => [
                        'offset' => true
                    ]
                ],
                'y' => [
                    'beginAtZero' => true
                ]
            ],
        ]);

        return $this->render('front/accountant/index.html.twig', [
            'barChart' => $barChart,
            'lineChart' => $lineChart,
            'doughnutChart' => $doughnutChart,
            'data' => $data,
        ]);
    }
}
