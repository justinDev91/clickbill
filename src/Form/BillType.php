<?php

namespace App\Form;

use App\Entity\Bill;
use App\Entity\Company;
use App\Entity\Quote;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $company = $options['company'];
        $builder
            ->add('quote', EntityType::class, [
                'label' => 'Devis',
                'class' => Quote::class,
                'choice_label' => function ($quote) {
                    return  '#' . $quote->getId() . ' ' . $quote->getClient();
                },
                'query_builder' => function (EntityRepository $er) use ($company) {
                    return $er->createQueryBuilder('quote')
                        ->andWhere('quote.company = :company')
                        ->andWhere('quote.isDeleted = false')
                        ->setParameter('company', $company)
                        ->orderBy('quote.id', 'DESC');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bill::class,
            'company' => Company::class,
        ]);
    }
}
