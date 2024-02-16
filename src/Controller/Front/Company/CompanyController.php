<?php

namespace App\Controller\Front\Company;

use App\Repository\BillRepository;
use App\Repository\ClientInteractionRepository;
use App\Repository\ClientRepository;
use App\Repository\QuoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class CompanyController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function chart(
        ChartBuilderInterface $chartBuilder,
        ClientRepository $clientRepository,
        BillRepository $billRepository,
        QuoteRepository $quoteRepository,
        ClientInteractionRepository $clientInteractionRepository
    ): Response {

        $company = $this->getUser()->getCompany();

        $data = [
            'clients' => $clientRepository->findActiveClientsByCompany($company),
            'clientsInteractions' => $clientInteractionRepository->findAllClientsInteractionsByCompany($company),
            'totalClients' => $clientRepository->getTotalClientCountForCompany($company),
            'totalBills' => $billRepository->getTotalBillCountByStatusForCompany($company),
            'totalUnpaidBills' => $billRepository->getTotalBillCountByStatusForCompany($company, 'unpaid'),
            'totalPaidBills' => $billRepository->getTotalBillCountByStatusForCompany($company, 'paid'),
            'totalQuotes' => $quoteRepository->getTotalQuoteCountForCompany($company),
        ];

        $barChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $lineChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $doughnutChart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);

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

        return $this->render('front/company/dashboard.html.twig', [
            'barChart' => $barChart,
            'lineChart' => $lineChart,
            'doughnutChart' => $doughnutChart,
            'data' => $data,
        ]);
    }
}
