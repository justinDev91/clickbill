<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class BillFilterType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('status', ChoiceType::class, [
        'choices' => [
          "Attente de l'acompte" => "En attente du paiement de l'acompte",
          'Prête' => 'Prête à l\'envoi',
          'Attente paiement' => 'En attente de paiement',
          'Acquitée' => 'Acquitté',
          'Non acquittée' => 'Non acquitté'
        ],
        'label' => false,
        'placeholder' => 'Status',
        'attr' => [
          'id' => 'filter-select',
          'class' => 'flex-shrink-0 z-10 inline-flex items-center py-2.5 px-4 text-sm font-medium text-center text-gray-900 bg-gray-100 border border-gray-300 rounded-s-lg hover:bg-gray-200 focus:ring-yellow-400 focus:outline-none dark:bg-gray-700 dark:hover:bg-gray-600  dark:text-white dark:border-gray-600',
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
