<?php

namespace App\Controller\Public;

use App\Form\RegistrationFormType;
use App\Security\AppUserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// TODO : Redirect to 'dashboard' page when already logged. CRSF TOKEN FRONT ?

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        # TODO : Send mail
        if ($this->getUser()) {
            return $this->redirectToRoute('front_dashboard_index');
        }
        
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get datas from form
            $formData = $form->getData();
            
            // send mail to our services in order to contact user who want to create an account.
            // redirect with success message or error message.
            return $this->redirectToRoute('app_login');
        }

        return $this->render('public/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route('/reset-password', name: 'app_reset_password')]
    public function resetPassword(Request $request): Response
    {
        # TODO : Reset password form and send mail.
        if ($this->getUser()) {
            return $this->redirectToRoute('front_dashboard_index');
        }
        
        $form = $this->createForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get datas from form
            $formData = $form->getData();
            
            // redirect with success message or error message.
            return $this->redirectToRoute('app_login');
        }

        return $this->render('public/registration/reset_password.html.twig', [
            'resetPasswordForm' => $form->createView(),
        ]);
    }
}
