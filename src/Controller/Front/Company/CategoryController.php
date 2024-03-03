<?php

namespace App\Controller\Front\Company;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Form\StatusFilterType;
use App\Form\CustomSearchFormType;
use App\Service\PaginationService;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/category')]
#[Security('is_granted("ROLE_COMPANY")')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'app_category_index', methods: ['GET', 'POST'])]
    public function index(
        CategoryRepository $categoryRepository,
        Request $request,
        PaginationService $paginationService
    ): Response {
        $company = $this->getUser()->getCompany();

        $categories = $categoryRepository->getAllActiveCategories($company);

        //Search
        $searchForm = $this->createForm(CustomSearchFormType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchTerm = strtolower($searchForm->get('search')->getData());
            $categories = $categoryRepository->searchCategoriesByNameOrDescription($searchTerm, $company);
        }

        $filterForm = $this->createForm(StatusFilterType::class);
        $filterForm->handleRequest($request);

        $pagination = $paginationService->paginate($categories, $request);

        return $this->render(
            'front/category/index.html.twig', 
            [
            'categories' => $categories,
            'controller_name' => 'CategoryController',
            'searchForm' => $searchForm,
            'filterForm' => $filterForm,
            'pagination' => $pagination,
            'buttonPath' => 'front_company_app_categorie_new',
            'buttonLabel' => 'Ajouter une catégorie'
            ]
        );
    }

    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    // #[Security('is_granted("ROLE_COMPANY")')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $category = new Category();
        $currentUserId = $this->getUser()->getId();
        $company = $this->getUser()->getCompany();

        $category
            ->setIsDeleted(false)
            ->setCreatedBy($currentUserId)
            ->setCreatedAt(new \DateTime())
            ->setCompany($company);

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', "La catégorie {$category->getName()} a bien été créée");

            return $this->redirectToRoute(
                'front_company_app_category_index', 
                ['id' => $category->getId()]
            );
        }

        return $this->render(
            'front/category/new.html.twig', 
            [
                'category' => $category,
                'form' => $form,
            ]
        );
    }

    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render(
            'front/category/show.html.twig',
            [
                'category' => $category,
            ]
        );
    }

    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    // #[Security('is_granted("ROLE_COMPANY")')]
    public function edit(
        Request $request,
        Category $category,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        $currentUserId = $this->getUser()->getId();
        $category->setUpdatedBy($currentUserId);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', "La catégorie {$category->getName()} a bien été modifiée");

            return $this->redirectToRoute(
                'front_company_app_category_index',
                ['id' => $category->getId()]
            );
        }

        return $this->render('front/category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_category_delete', methods: ['POST'])]
    // #[Security('is_granted("ROLE_COMPANY")')]
    public function delete(
        Request $request,
        Category $category,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {

            $category->setIsDeleted(true);

            $entityManager->persist($category);
            $entityManager->flush();

            $this->addFlash('success', "La catégorie {$category->getName()} a bien été supprimée");
        }

        return $this->redirectToRoute('front_company_app_category_index');
    }
}
