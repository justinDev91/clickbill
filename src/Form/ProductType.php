<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Company;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class, 
                [
                    'label' => 'Nom du produit',
                    'attr' => [
                        'placeholder' => 'Nom du produit'
                    ]
                ]
            )
            ->add(
                'price',
                NumberType::class, 
                [
                    'label' => 'Prix',
                    'attr' => [
                        'placeholder' => 'Prix'
                    ]
                ]
            )
            ->add(
                'description',
                TextType::class, 
                [
                    'label' => 'Description',
                    'attr' => [
                        'placeholder' => 'Description'
                    ]
                ]
            )
            ->add(
                'imageFile', VichImageType::class, 
                [
                    'required' => false,
                    'allow_delete' => true,
                    'download_uri' => true,
                    'image_uri' => true,
                ]
            )
            // ->add(
            //     'category', 
            //     EntityType::class, 
            //     [
            //         'class' => Category::class,
            //         'choice_label' => 'id',
            //     ])
            ->add('category', EntityType::class, 
                [
                    'label' => 'Catégorie du produit',
                    'class' => Category::class,
                    'choice_label' => 'name',
                    'query_builder' => fn (CategoryRepository $categoryRepository) => $categoryRepository->createQueryBuilder('c')->orderBy('c.name', 'ASC'),
                ])
            // ->add(
            //     'company',
            //     EntityType::class,
            //     [
            //         'class' => Company::class,
            //         'choice_label' => 'id',
            //     ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
