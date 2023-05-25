<?php

namespace App\Form;

use App\Entity\Asset;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('serialnumber', TextType::class, [
                'label' => 'Serial Number',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('assettag', TextType::class, [
                'label' => 'Asset Tag',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('purchasedate', DateTimeType::class, [
                'label' => 'Purchased Date',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('purchasedfrom', TextType::class, [
                'label' => 'Purchased From',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('warrantystartdate', DateTimeType::class, [
                'label' => 'Warranty Start',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('warrantyenddate', DateTimeType::class, [
                'label' => 'Warranty End',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('condition', TextType::class, [
                'label' => 'Condition',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('make', TextType::class, [
                'label' => 'Make',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('model', TextType::class, [
                'label' => 'Model',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('decomisioned', CheckboxType::class, [
                'label' => 'Decommissioned',
                'row_attr' => [
                    'class' => 'form-check-inline'
                ],
                'required' => false,
                'label_attr' => [
                    'class' => 'form-check-label mr-2 my-2'
                ],
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Asset::class,
        ]);
    }
}
