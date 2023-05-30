<?php

namespace App\Form;

use App\Repository\SiteConfigRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetCollectionType extends AbstractType
{
    public function __construct(private readonly SiteConfigRepository $siteConfigRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $uniqueIdentifier = $this->siteConfigRepository->findOneBy(['configName' => 'asset_unique_identifier'])->getConfigValue();
        $builder
            ->add('device')
            ->add('user')
            ->add('location')
            ->add('notes')
            ->add('Collect', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
