<?php

namespace App\Form;

use App\Entity\Company;
use App\Entity\Product;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $company = $options['company'];

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
            ->add('category', EntityType::class, 
                [
                    'label' => 'Catégorie du produit',
                    'class' => Category::class,
                    'choice_label' => 'name',
                    'query_builder' => function (CategoryRepository $categoryRepository) use ($company) {
                        return $categoryRepository->createQueryBuilder('c')
                            ->andWhere('c.isDeleted = false')
                            ->andWhere('c.company = :company')
                            ->setParameter('company', $company)
                            ->orderBy('c.name', 'ASC');
                    }
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'company' => Company::class
        ]);
    }
}
