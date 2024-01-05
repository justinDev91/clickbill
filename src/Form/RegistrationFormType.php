<?php

namespace App\Form;

use App\Validator\EmailExists;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;


class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le champ email ne peut pas être vide.']),
                    new Email(['message' => "L'adresse email est invalide."]),
                    new EmailExists(),
                ],
            ])
            ->add('phone', TelType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro de téléphone ne peut pas être vide.']),
                    new Regex([
                        'pattern' => '/^[0-9+\-() ]*$/',
                        'message' => "Le numéro de téléphone n'est pas valide.",
                    ]),
                    new Length([
                        'min' => 5,
                        'max' => 15,
                        'minMessage' => 'Le numéro de téléphone doit avoir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le numéro de téléphone ne peut pas avoir plus de {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('infos', TextareaType::class, [
                'constraints' => [
                    new NotBlank(['message' => "Le champ d'informations ne peux pas être vide."]),
                    new Regex([
                        'pattern' => '/^[^<>\[\]\{\}]*$/',
                        'message' => "Le champ d'informations contiens des charactères invalides.",
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            
        ]);
    }
}
