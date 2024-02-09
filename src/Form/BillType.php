<?php

namespace App\Form;

use App\Entity\Bill;
use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Quote;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quote', EntityType::class, [
                'class' => Quote::class,
                'choice_label' => function ($quote) {
                    return  '#' . $quote->getId() . ' ' . $quote->getClient();
                },
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('quote')
                        ->orderBy('quote.id', 'DESC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bill::class,
        ]);
    }
}
