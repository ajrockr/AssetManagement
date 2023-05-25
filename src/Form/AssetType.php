<?php

namespace App\Form;

use App\Entity\Asset;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('serialnumber')
            ->add('assettag')
            ->add('purchasedate')
            ->add('purchasedfrom')
            ->add('warrantystartdate')
            ->add('warrantyenddate')
            ->add('condition')
            ->add('make')
            ->add('model')
            ->add('decomisioned')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Asset::class,
        ]);
    }
}
