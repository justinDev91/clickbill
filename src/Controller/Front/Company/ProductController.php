<?php

namespace App\Controller\Front\Company;

use App\Repository\ProductRepository;
use App\Entity\Product;
use App\Form\CategoryFilterType;
use App\Form\CustomSearchFormType;
use App\Form\ProductType;
use App\Repository\CategoryRepository;
use App\Service\InteractionService;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/product')]
#[Security('is_granted("ROLE_COMPANY")')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET', 'POST'])]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        Request $request,
        PaginationService $paginationService,
        SessionInterface $session
    ): Response {
        $company = $this->getUser()->getCompany();

        $products = $productRepository->getAllActiveProducts($company);

        $categories = $categoryRepository->getAllActiveCategories($company);

        //Searchs Products
        $searchForm = $this->createForm(CustomSearchFormType::class);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchTerm = strtolower($searchForm->get('search')->getData());
            $products = $productRepository->searchProductsByNameOrDescription($searchTerm,  $company);
        }

        //Category filter
        $filterForm = $this->createForm(CategoryFilterType::class, null, ['choices' => $categories]);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $category = $filterForm->get('Category')->getData();
            if ($category) {
                $products = $productRepository->filterProductsByCategory($category,  $company);
            }
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
                'buttonLabel' => 'Ajouter un produit',
                'errors' => $session->getFlashBag()->get('error', []),
                'success' => $session->getFlashBag()->get('success', []),
            ]
        );
    }
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    #[Security('is_granted("ROLE_COMPANY")')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManagerInterface,
        SessionInterface $session
    ): Response {
        try {
            $product = new Product();
            $currentUserId = $this->getUser()->getId();
            $company = $this->getUser()->getCompany();

            $form = $this->createForm(ProductType::class, $product, ['company' => $company]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $formData = $form->getData();
                $category = $formData->getCategory();
                $product
                    ->setIsDeleted(false)
                    ->setCreatedBy($currentUserId)
                    ->setCreatedAt(new \DateTime())
                    ->setCategory($category)
                    ->setCompany($company);

                $entityManagerInterface->persist($product);
                $entityManagerInterface->flush();

                $session->getFlashBag()->add('success', "Le produit {$product->getName()} a bien été créé");

                return $this->redirectToRoute(
                    'front_company_app_product_show',
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
        } catch (Exception $e) {
            $session->getFlashBag()->add('error', "Il y a eu une erreur lors de la création du produit");

            return $this->redirectToRoute(
                'front_company_app_product_index'
            );
        }
    }

    #[Route('/{slug}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render(
            'front/product/show.html.twig',
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
        SessionInterface $session
    ): Response {
        try {
            $company = $this->getUser()->getCompany();

            $form = $this->createForm(ProductType::class, $product, ['company' => $company]);
            $form->handleRequest($request);

            $currentUserId = $this->getUser()->getId();
            $product->setUpdatedBy($currentUserId);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManagerInterface->persist($product);
                $entityManagerInterface->flush();

                $session->getFlashBag()->add('success', "Le produit {$product->getName()} a bien été modifié");

                return $this->redirectToRoute(
                    'front_company_app_product_show',
                    ['slug' => $product->getSlug()]
                );
            }

            return $this->render('front/product/edit.html.twig', [
                'product', $product,
                'form' => $form
            ]);
        } catch (Exception $e) {
            $session->getFlashBag()->add('error', "Il y a eu une erreur lors de la modification du produit");

            return $this->redirectToRoute(
                'front_company_app_product_index'
            );
        }
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
