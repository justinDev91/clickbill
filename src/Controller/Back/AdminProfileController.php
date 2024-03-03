<?php

namespace App\Controller\Back;

use App\Form\AdminProfileType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile')]
#[Security('is_granted("ROLE_ADMIN")')]
class AdminProfileController extends AbstractController
{
    #[Route('/', name: 'app_profile_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('back/profile/index.html.twig');
    }

    #[Route('/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(AdminProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $newPassword = $form->get('password')->getData();
            $checkPassword = $form->get('checkPassword')->getData();

            if($newPassword && $checkPassword){
                if($newPassword === $checkPassword){
                    $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                    $user->setPassword($hashedPassword);
                } else {
                    $form->get('password')->addError(new FormError('Les mots de passe ne correspondent pas.'));
                    return $this->render('back/profile/edit.html.twig', [
                        'form' => $form,
                    ]);
                }
            }

            $entityManager->flush();
            return $this->redirectToRoute('back_app_dashboard', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/profile/edit.html.twig', [
            'form' => $form,
        ]);
    }
}
