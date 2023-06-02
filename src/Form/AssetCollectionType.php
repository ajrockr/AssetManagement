<?php

namespace App\Form;

use App\Entity\User;
use App\Repository\SiteConfigRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetCollectionType extends AbstractType
{
    private array $users;
    public function __construct(private readonly SiteConfigRepository $siteConfigRepository, private readonly UserRepository $userRepository)
    {
        // Populate user choices
        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            $this->users[$user->getId()] = $user->getSurname() . ', ' . $user->getFirstname();
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $uniqueIdentifier = $this->siteConfigRepository->findOneBy(['configName' => 'asset_unique_identifier'])->getConfigValue();
        $builder
            ->add('device', TextType::class, [
                'attr' => [
                    'autofocus' => true,
                    'class' => 'form-control',
                ]
            ])
            ->add('user', ChoiceType::class, [
                'choices' => [
                    array_combine(array_values($this->users), array_keys($this->users))
                ],
                'attr' => [
                    'class' => 'form-control js-example-basic-single',
                ]
            ])
            ->add('userId', HiddenType::class)
            ->add('assetId', HiddenType::class)
            ->add('needsrepair', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])
            ->add('location', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('Collect', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->add('checkout', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
