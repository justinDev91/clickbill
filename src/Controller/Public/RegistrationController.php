<?php

namespace App\Controller\Public;

use App\Entity\User;
use App\Service\MailService;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, MailService $mailService): Response
    {
        if ($this->getUser()) {
            # TODO : Redirect to another route according the role.
            return $this->redirectToRoute('front_company_app_dashboard');
        }
        
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get datas from form
            $formData = $form->getData();
            
            try {
                // Send email to us when user want to create new account.
                $mailService->sendTemplatedEmail(
                    $_ENV['CLICKBILL_MAIL'],
                    'Demande de création de compte | Clickbill',
                    'emails/registration.html.twig',
                    [
                        'content' => 'Un nouvel utilisateur veux créer un compte sur la plateforme.',
                        'emailContact' => $formData['email'],
                        'phone' => $formData['phone'],
                        'infos' => $formData['infos'],
                    ]
                );

                // redirect with success message
                $this->addFlash('success', "L'email avec votre nouveau mot de passe à bien été envoyer.");
                return $this->redirectToRoute('app_login');
            } catch (\Exception $exception) {
                $this->addFlash('error', "Une erreur est survenu lors de l'envoi de l'email, veuillez réessayer plus tard.");
            };
        }

        return $this->render('public/registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    #[Route('/reset-password', name: 'app_reset_password')]
    public function resetPassword(Request $request, MailService $mailService, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser()) {
            # TODO : Redirect to another route according the role.
            return $this->redirectToRoute('front_app_company_dashboard');
        }
        
        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get datas from form
            $formData = $form->getData();
            
            // Get the user.
            $user = $entityManager
                ->getRepository(User::class)
                ->findOneByEmail(strtolower($formData['email']));

            if($user){
                // Generate a random password
                $newPassword = bin2hex(random_bytes(8));
                
                try {
                    $mailService->sendTemplatedEmail(
                        $formData['email'],
                        'Demande de réinitialisation de mot de passe | Clickbill',
                        'emails/reset_password.html.twig',
                        [
                            'content' => 'Vous avez demander de réinitialiser votre mot de passe.',
                            'emailContact' => $formData['email'],
                            'newPassword' => $newPassword,
                        ]
                    );

                    // Hash and set the new password for the user
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                    $user->setPassword($hashedPassword);
                    // persist and flush only if the email was sent successfully
                    $entityManager->persist($user);
                    $entityManager->flush();

                    // redirect with success message
                    $this->addFlash('success', "L'email avec votre nouveau mot de passe à bien été envoyer.");
                    return $this->redirectToRoute('app_login');
                } catch (\Exception $exception) {
                    $this->addFlash('error', "Une erreur est survenu lors de l'envoi de l'email, veuillez réessayer plus tard.");
                }
            } else {
                $this->addFlash('error', "Une erreur est survenu, il semblerais que l'utilisateur n'existe pas.");
            };
        }

        return $this->render('public/registration/reset_password.html.twig', [
            'resetPasswordForm' => $form->createView(),
        ]);
    }
}
