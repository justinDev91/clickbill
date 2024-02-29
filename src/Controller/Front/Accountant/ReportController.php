<?php

namespace App\Controller\Front\Accountant;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReportController extends AbstractController
{
    #[Route('/report', name: 'app_report_index')]
    public function index(): Response
    {
        return $this->render('front/report/index.html.twig');
    }
}
