<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType as SearchT;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Regex;

class SearchType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      ->add('search', SearchT::class, [
        'label' => 'Search',
        'required' => true,
        'constraints' => [
          new NotBlank(),
          new Type(['type' => 'string']),
          new Regex([
            'pattern' => "/^[a-zA-Z0-9\-\' ]+$/",
            'message' => "Invalid characters in search. Only alphanumeric characters, hyphens, single quotes, and spaces are allowed."
          ]),
        ],
      ]);
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults([]);
  }
}
