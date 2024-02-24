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
                    'Brouillon' => "brouillon",
                    'En cours' => 'en cours',
                    'Validé' => 'validé',
                    'Annulé' => 'annulé',
                ],
                'constraints' => [
                    new Choice([
                        'choices' => ['en cours', 'validé', 'annulé', 'brouillon'],
                        'message' => 'Invalid status value.',
                    ]),
                ],
                'label' => false,
                'placeholder' => 'Status',
                'attr' => [
                    'id' => 'filter-select',
                    'class' => '
                        flex-shrink-0 z-10 inline-flex items-center py-2.5 px-4 text-sm font-medium text-center border rounded-s-lg
                        bg-neutral-100 text-neutral-900 border-neutral-300 focus:ring-0 focus:border-neutral-300
                        dark:bg-neutral-700 dark:text-neutral-200 dark:border-neutral-700
                    ',
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
