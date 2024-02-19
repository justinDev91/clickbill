<?php

namespace App\Controller\Front\Company;

use App\Repository\ProductRepository;
use App\Entity\Product;
use App\Form\CategoryFilterType;
use App\Form\CustomSearchFormType;
use App\Form\ProductType;
use App\Form\StatusFilterType;
use App\Service\InteractionService;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/product')]
#[Security('is_granted("ROLE_USER")')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET', 'POST'])]
    public function index(
        ProductRepository $productRepository,
        Request $request,
        PaginationService $paginationService
    ): Response {
        $company = $this->getUser()->getCompany();

        $products = $productRepository->getAllActiveProducts($company);

        //Searchs Products
        $searchForm = $this->createForm(CustomSearchFormType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchTerm = strtolower($searchForm->get('search')->getData());
            $products = $productRepository->searchProductsByNameOrDescription($searchTerm,  $company);
        }

        //Category filter
        $filterForm = $this->createForm(CategoryFilterType::class);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $category = $filterForm->get('category')->getData();
            if ($category) $products = $productRepository->filterProductsByCategory($category,  $company);
        }

        $pagination = $paginationService->paginate($products, $request);

        return $this->render(
            'front/product/index.html.twig',
            [
                'controller_name' => 'ProductController',
                'products' => $products,
                'searchForm' => $searchForm,
                'filterForm' => $filterForm,
                'pagination' => $pagination,
                'buttonPath' => 'front_company_app_product_new',
                'buttonLabel' => 'Ajouter un produit'
            ]
        );
    }
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    #[Security('is_granted("ROLE_COMPANY")')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        InteractionService $interactionService
    ): Response {
        $product = new Product();
        $currentUserId = $this->getUser()->getId();
        $company = $this->getUser()->getCompany();

        $product
            ->setIsDeleted(false)
            ->setCreatedBy($currentUserId)
            ->setCreatedAt(new \DateTime())
            ->setCompany($company);

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManagerInterface->persist($product);
            $entityManagerInterface->flush();

            $this->addFlash('success', "Le produit {$product->getName()} a bien été créé");

            return $this->redirectToRoute(
                'front_company_app_product_details',
                ['slug' => $product->getSlug()]
            );
        }

        return $this->render(
            'front/product/new.html.twig',
            [
                'product' => $product,
                'form' => $form,
            ]
        );
    }

    #[Route('/{slug}', name: 'app_product_details', methods: ['GET'])]
    public function details(Product $product): Response
    {
        return $this->render(
            'front/product/details.html.twig',
            [
                'product' => $product
            ]
        );
    }

    #[Route('/{slug}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    #[Security('is_granted("ROLE_COMPANY")')]
    public function edit(
        Product $product,
        EntityManagerInterface $entityManagerInterface,
        Request $request,
    ): Response {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        $currentUserId = $this->getUser()->getId();
        $product->setUpdatedBy($currentUserId);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManagerInterface->persist($product);
            $entityManagerInterface->flush();

            $this->addFlash('success', "Le produit {$product->getName()} a bien été modifié");

            return $this->redirectToRoute(
                'front_company_app_product_details',
                ['slug' => $product->getSlug()]
            );
        }

        return $this->render('front/product/edit.html.twig', [
            'product', $product,
            'form' => $form
        ]);
    }

    #[Route('/{slug}/delete', name: 'app_product_delete', methods: ['POST'])]
    #[Security('is_granted("ROLE_COMPANY")')]
    public function delete(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManagerInterface
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $product->getSlug(), $request->request->get('_token'))) {

            $product->setIsDeleted(true);

            $entityManagerInterface->persist($product);
            $entityManagerInterface->flush();

            $this->addFlash('success', "Le produit {$product->getName()} a bien été supprimée");
        }

        return $this->redirectToRoute('front_company_app_product_index');
    }
}
