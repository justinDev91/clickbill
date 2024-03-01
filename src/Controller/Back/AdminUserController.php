<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Form\AdminFilterType;
use App\Form\CustomSearchFormType;
use App\Service\MailService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/user')]
#[Security('is_granted("ROLE_ADMIN")')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET', 'POST'])]
    public function index(Request $request, UserRepository $userRepository, SessionInterface $session): Response
    {
        // Get all users even not active excepted the admin logged (because update in settings page).
        $users = $userRepository->findAllUsersWithoutLoggedAdmin($this->getUser()->getId());

        //Search User
        $searchForm = $this->createForm(CustomSearchFormType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchTerm = strtolower($searchForm->get('search')->getData());
            $users = $userRepository->searchUserByNameOrEmailOrCompanyName($this->getUser()->getId(), $searchTerm);
        }

        //Status filter
        $filterForm = $this->createForm(AdminFilterType::class);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $status = $filterForm->get('status')->getData();
            if ($status) $users = $userRepository->filterUsersByStatus($this->getUser()->getId(), $status);
        }
        
        return $this->render('back/user/index.html.twig', [
            'users' => $users,
            'filterForm' => $filterForm,
            'searchForm' => $searchForm,
            'errors' => $session->getFlashBag()->get('error', []),
            'success' => $session->getFlashBag()->get('success', [])
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get datas from form
            $formData = $form->getData();
            $connectedUser = $this->getUser();

            // Generate random password for user.
            $newPassword = bin2hex(random_bytes(8));
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            // User
            $user
                ->setFirstName($formData->getFirstName())
                ->setLastName($formData->getFirstName())
                ->setEmail($formData->getEmail())
                ->setPassword($hashedPassword)
                ->setCompany($formData->getCompany())
                ->setRoles([$form->get('role')->getData()])
                ->setUpdatedBy($connectedUser->getId())
                ->setCreatedBy($connectedUser->getId());

            $entityManager->persist($user);
            $entityManager->flush();

            $session->getFlashBag()->add('success', "L'utilisateur a bien été crée.");
            return $this->redirectToRoute('back_app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user, UserRepository $userRepository, SessionInterface $session): Response
    {
        $createdByUser = $userRepository->find($user->getCreatedBy());
        $createdBy = $createdByUser ? ['name' => $createdByUser->getDisplayName()] : ['name' => ''];
        return $this->render('back/user/show.html.twig', [
            'user' => $user,
            'created_by' => $createdBy,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $user_roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $user_roles)) {
            $highestRole = 'ROLE_ADMIN';
        } elseif (in_array('ROLE_COMPANY', $user_roles)) {
            $highestRole = 'ROLE_COMPANY';
        } elseif (in_array('ROLE_ACCOUNTANT', $user_roles)) {
            $highestRole = 'ROLE_ACCOUNTANT';
        } else {
            $highestRole = 'ROLE_COMPANY'; // Default
        }

        $form = $this->createForm(AdminUserType::class, $user, [
            'highest_role' => $highestRole,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get datas from form
            $formData = $form->getData();
            $connectedUser = $this->getUser();

            // User
            $user
                ->setFirstName($formData->getFirstName())
                ->setLastName($formData->getFirstName())
                ->setEmail($formData->getEmail())
                ->setCompany($formData->getCompany())
                ->setRoles([$form->get('role')->getData()])
                ->setUpdatedBy($connectedUser->getId());

            $entityManager->flush();
            $session->getFlashBag()->add('success', "L'utilisateur a bien été modifié.");
            return $this->redirectToRoute('back_app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            if($user->isIsDeleted()){
                $session->getFlashBag()->add('error', "L'utilisateur a déjà été supprimée.");
            } else {
                $user->setIsDeleted(true);
                $entityManager->flush();
                $session->getFlashBag()->add('success', "L'utilisateur a bien été supprimée.");
            }
        }

        return $this->redirectToRoute('back_app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/enable', name: 'app_user_enable', methods: ['POST'])]
    public function enable(Request $request, User $user, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        if ($this->isCsrfTokenValid('enable'.$user->getId(), $request->request->get('_token'))) {
            if($user->isIsDeleted()){
                $user->setIsDeleted(false);
                $entityManager->flush();
                $session->getFlashBag()->add('success', "L'utilisateur a bien été réactivé.");
            } else {
                $session->getFlashBag()->add('error', "L'utilisateur a déjà été réactivé.");
            }
        }

        return $this->redirectToRoute('back_app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/send-mail', name: 'app_user_send_mail', methods: ['POST'])]
    public function send_mail(Request $request, User $user, EntityManagerInterface $entityManager, SessionInterface $session, MailService $mailService, UserPasswordHasherInterface $passwordHasher, UrlGeneratorInterface $urlGenerator): Response
    {
        if ($this->isCsrfTokenValid('send_mail'.$user->getId(), $request->request->get('_token'))) {
            if($user->isIsDeleted()){
                $session->getFlashBag()->add('error', "Vous ne pouvez pas envoyer de mail à un utilisateur supprimé.");
            } else {
                // Generate new password for user in order to send it an readable one in email.
                $newPassword = bin2hex(random_bytes(8));
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);

                $loginUrl = $urlGenerator->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
                try {
                    $mailService->sendTemplatedEmail(
                        $user->getEmail(),
                        'Votre nouveau compte | Clickbill',
                        'emails/new_account.html.twig',
                        [
                            'loginEmail' => $user->getEmail(),
                            'loginPassword' => $newPassword,
                            'loginUrl' => $loginUrl,
                        ]
                    );

                    $entityManager->flush();
                    $session->getFlashBag()->add('success', "L'email de connexion à bien été envoyé.");
                } catch (\Exception $exception) {
                    $session->getFlashBag()->add('error', "Une erreur est survenu lors de l'envoi du mail de connexion à l'utilisateur.");
                }
            }
        }

        return $this->redirectToRoute('back_app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
