<?php

namespace App\Controller\Front\Accountant;

use App\Repository\BillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BillController extends AbstractController
{
    #[Route('/', name: 'app_bill_index', methods: ['GET'])]
    public function index(BillRepository $billRepository): Response
    {
        $company = $this->getUser()->getCompany();
        $bills = $billRepository->findAllActiveBillsByCompany($company);

        return $this->render('front/bill/index.html.twig', [
            'bills' => $bills,
        ]);
    }
}
