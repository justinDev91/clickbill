<?php

namespace App\Controller\Back;

use App\Entity\Company;
use App\Form\AdminCompanyType;
use App\Form\AdminFilterType;
use App\Form\CustomSearchFormType;
use App\Repository\UserRepository;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/company')]
#[Security('is_granted("ROLE_ADMIN")')]
class AdminCompanyController extends AbstractController
{
    #[Route('/', name: 'app_company_index', methods: ['GET', 'POST'])]
    public function index(Request $request, CompanyRepository $companyRepository, SessionInterface $session): Response
    {
        # Get all company (even not active)
        $companies = $companyRepository->findAll();

        //Search Company
        $searchForm = $this->createForm(CustomSearchFormType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchTerm = strtolower($searchForm->get('search')->getData());
            $companies = $companyRepository->searchCompanyByNameOrEmailOrPhone($searchTerm);
        }

        //Status filter
        $filterForm = $this->createForm(AdminFilterType::class);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $status = $filterForm->get('status')->getData();
            if ($status) $companies = $companyRepository->filterCompaniesByStatus($status);
        }

        return $this->render('back/company/index.html.twig', [
            'companies' => $companies,
            'filterForm' => $filterForm,
            'searchForm' => $searchForm,
            'errors' => $session->getFlashBag()->get('error', []),
            'success' => $session->getFlashBag()->get('success', [])
        ]);
    }

    #[Route('/new', name: 'app_company_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $company = new Company();
        $form = $this->createForm(AdminCompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get datas from form
            $formData = $form->getData();
            $connectedUser = $this->getUser();

            // Company
            $company
                ->setName($formData->getName())
                ->setAddress($formData->getAddress())
                ->setEmail($formData->getEmail())
                ->setPhone($formData->getPhone())
                ->setUpdatedBy($connectedUser->getId())
                ->setCreatedBy($connectedUser->getId());

            $entityManager->persist($company);
            $entityManager->flush();

            $session->getFlashBag()->add('success', "La company a bien été créée.");
            return $this->redirectToRoute('back_app_company_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/company/new.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_company_show', methods: ['GET'])]
    public function show(Company $company, UserRepository $userRepository): Response
    {
        $createdByUser = $userRepository->find($company->getCreatedBy());
        $createdBy = $createdByUser ? ['name' => $createdByUser->getDisplayName()] : ['name' => ''];
        return $this->render('back/company/show.html.twig', [
            'company' => $company,
            'created_by' => $createdBy,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_company_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Company $company, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $form = $this->createForm(AdminCompanyType::class, $company);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get datas from form
            $formData = $form->getData();
            $connectedUser = $this->getUser();

            // Company
            $company
                ->setName($formData->getName())
                ->setAddress($formData->getAddress())
                ->setEmail($formData->getEmail())
                ->setPhone($formData->getPhone())
                ->setUpdatedBy($connectedUser->getId())
                ->setCreatedBy($connectedUser->getId());

            $entityManager->flush();
            $session->getFlashBag()->add('success', "La company a bien été modifiée.");
            return $this->redirectToRoute('back_app_company_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/company/edit.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_company_delete', methods: ['POST'])]
    public function delete(Request $request, Company $company, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        if ($this->isCsrfTokenValid('delete'.$company->getId(), $request->request->get('_token'))) {
            if($company->isIsDeleted()){
                $session->getFlashBag()->add('error', "La company a déjà été supprimée.");
            } else {
                $company->setIsDeleted(true);
                $entityManager->flush();
                $session->getFlashBag()->add('success', "La company a bien été supprimée.");
            }
        }

        return $this->redirectToRoute('back_app_company_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/enable', name: 'app_company_enable', methods: ['POST'])]
    public function enable(Request $request, Company $company, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        if ($this->isCsrfTokenValid('enable'.$company->getId(), $request->request->get('_token'))) {
            if($company->isIsDeleted()){
                $company->setIsDeleted(false);
                $entityManager->flush();
                $session->getFlashBag()->add('success', "La company a bien été réactivée.");
            } else {
                $session->getFlashBag()->add('error', "La company a déjà été réactivée.");
            }
        }

        return $this->redirectToRoute('back_app_company_index', [], Response::HTTP_SEE_OTHER);
    }
}
