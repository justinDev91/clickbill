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
          'placeholder' => 'Search',
          'class' => 'block p-2.5 w-full z-20 text-sm text-gray-900 bg-gray-50 rounded-e-lg border-s-gray-50 border-s-2 border border-gray-300 focus:ring-yellow-400  dark:bg-gray-700 dark:border-s-gray-700  dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:border-gray-500',
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
