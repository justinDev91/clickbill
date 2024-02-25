<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;

class CustomSearchFormType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('search', SearchType::class, [
        'label' => false,
        'attr' => [
          'placeholder' => 'Rechercher',
          'class' => '
            block p-2.5 w-full z-20 text-sm rounded-e-lg border
            text-neutral-900 border border-neutral-300 focus:ring-0 focus:border-neutral-300
            dark:bg-neutral-900 dark:border-neutral-700 dark:placeholder-neutral-400 dark:text-neutral-200',
        ],
        'required' => true,
        'constraints' => [
          new NotBlank(),
          new Type(['type' => 'string']),
          new Regex([
            'pattern' => "/^[a-zA-Z0-9\-\' ]+$/",
            'message' => "Invalid characters in search. Only alphanumeric characters, hyphens, single quotes, and spaces are allowed."
          ]),
        ],
      ])
      ->add('submit', SubmitType::class, [
        'label' => false,
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'placeholder' => 'Search',
    ]);
  }
}
