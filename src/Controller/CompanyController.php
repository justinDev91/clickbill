<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;


#[Route('/company')]
class CompanyController extends AbstractController
{
    #[Route('/dashboard', name: 'app_company_dashboard')]
    public function chart(ChartBuilderInterface $chartBuilder, ClientRepository $clientRepository): Response
    {
        //TODO: Get company client include bill and devis
        $clients = $clientRepository->findAll();

        $barChart = $chartBuilder->createChart(Chart::TYPE_BAR);

        $lineChart = $chartBuilder->createChart(Chart::TYPE_LINE);

        $doughnutChart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);

        $doughnutChart->setData([
            'labels' => ['Le total des devis', 'Le total des factures payés', 'Le total des factures impayés'],
            'datasets' => [
                [
                    'label' => 'Le nombre total des devis validés par mois',
                    'backgroundColor' => ['rgb(255, 99, 132)', 'rgb(54, 162, 235)', 'rgb(246, 195, 30)'],
                    'data' => [30, 20, 5],
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

        return $this->render('company/dashboard.html.twig', [
            'barChart' => $barChart,
            'lineChart' => $lineChart,
            'doughnutChart' => $doughnutChart,
            'clients' => $clients,
        ]);
    }
}
