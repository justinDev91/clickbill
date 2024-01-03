<?php

namespace App\Form;

use App\Entity\Bill;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity')
            ->add('totalAmount')
            ->add('date')
            ->add('description')
            ->add('status')
            ->add('isDownPayment')
            ->add('createdBy')
            ->add('createdAt')
            ->add('updatedBy')
            ->add('updatedAt')
            ->add('isDeleted')
            ->add('company')
            ->add('client')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bill::class,
        ]);
    }
}
