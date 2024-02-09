<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Product;
use App\Entity\Quote;
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
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'mapped' => false,
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'name',
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quote::class,
        ]);
    }
}
