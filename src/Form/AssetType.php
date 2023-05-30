<?php

namespace App\Form;

use App\Entity\Asset;
use App\Repository\SiteConfigRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetType extends AbstractType
{
    public function __construct(private readonly SiteConfigRepository $siteConfigRepository) {}
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $assetUniqueIdentifier = $this->siteConfigRepository->findOneBy(['configName' => 'asset_unique_identifier'])->getConfigValue();
        $builder
            ->add('serialnumber', TextType::class, [
                'required' => 'serialnumber' == $assetUniqueIdentifier,
                'label' => 'Serial Number',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('assettag', TextType::class, [
                'required' => 'assettag' == $assetUniqueIdentifier,
                'label' => 'Asset Tag',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('purchasedate', DateType::class, [
                'required' => false,
                'input' => 'datetime_immutable',
                'label' => 'Purchased Date',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control js-datepicker'
                ]
            ])
            ->add('purchasedfrom', TextType::class, [
                'required' => false,
                'label' => 'Purchased From',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('warrantystartdate', DateType::class, [ // TODO: Allow warrantystart/end to also be null
                'required' => false,
                'input' => 'datetime_immutable',
                'label' => 'Warranty Start',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('warrantyenddate', DateType::class, [
                'required' => false,
                'input' => 'datetime_immutable',
                'label' => 'Warranty End',
                'widget' => 'single_text',
                'error_bubbling' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('condition', TextType::class, [
                'required' => false,
                'label' => 'Condition',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('make', TextType::class, [
                'required' => false,
                'label' => 'Make',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('model', TextType::class, [
                'required' => false,
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
