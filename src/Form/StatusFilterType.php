<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class StatusFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'En cours' => 'en cours',
                    'Validé' => 'valide',
                    'Refusé' => 'refuse',
                ],
                'constraints' => [
                    new Choice([
                        'choices' => ['en cours', 'valide', 'refuse'],
                        'message' => 'Invalid status value.',
                    ]),
                ],
                'label' => false,
                'placeholder' => 'Status',
                'attr' => [
                    'class' => 'text-gray-600 bg-gray-200 font-medium rounded-lg text-sm px-5 py-1.5 text-center inline-flex items-center dark:hover:bg',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
