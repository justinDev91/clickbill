<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Product;
use App\Entity\Quote;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;


class QuoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $company = $options['company'];

        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'mapped' => false,
                'query_builder' => function (EntityRepository $er) use ($company){
                    return $er->createQueryBuilder('p')
                            ->andWhere('p.company = :company')
                            ->setParameter('company', $company)
                            ->orderBy('p.name', 'ASC');
                }
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => function ($client) {
                    return $client->getDisplayName();
                },
                'query_builder' => function (EntityRepository $er) use ($company) {
                    return $er->createQueryBuilder('c')
                        ->andWhere('c.company = :company')
                        ->setParameter('company', $company)
                        ->orderBy('c.firstName', 'ASC')
                        ->addOrderBy('c.lastName', 'ASC');
                },
            ])
            ->add('downPayment', NumberType::class, [
                'scale' => 2,
                'required' => false,
                'constraints' => [
                    new Type([
                        'type' => 'numeric',
                        'message' => 'L\'acompte doit être un nombre valide.',
                    ]),
                    new LessThanOrEqual([
                        'value' => 80,
                        'message' => 'L\'acompte ne doit pas dépasser 80%.',
                    ]),
                ],
            ])
            ->add('tva', NumberType::class, [
                'constraints' => [
                    new Type([
                        'type' => 'numeric',
                        'message' => 'La TVA doit être un nombre valide.',
                    ]),
                    new LessThanOrEqual([
                        'value' => 27,
                        'message' => 'La TVA ne doit pas dépasser 27%.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quote::class,
            'company' => Company::class
        ]);
    }
}
