<?php

namespace App\Controller\Front\Accountant;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('front/user/index.html.twig', [
            'profile' => $userRepository->findOneBy(['id' => $this->getUser()->getId()]),
        ]);
    }
}
