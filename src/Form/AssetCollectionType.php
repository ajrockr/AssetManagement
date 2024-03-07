<?php

namespace App\Form;

use App\Entity\RepairParts;
use App\Entity\User;
use App\Repository\RepairPartsRepository;
use App\Repository\SiteConfigRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssetCollectionType extends AbstractType
{
    private array $users;

    private array $repairParts;
    public function __construct(
        private readonly SiteConfigRepository $siteConfigRepository,
        private readonly UserRepository $userRepository)
    {
        // Populate user choices
        $users = $this->userRepository->findAll();
        if (count($users) > 1) {
            foreach ($users as $user) {
                $this->users[$user->getId()] = $user->getSurname() . ', ' . $user->getFirstname() . ' (' . $user->getTitle() . ')';
            }
        } else {
            $this->users[0] = 'No Users Exists';
        }

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Will not longer be config. Now, just one of them has to be filled in
        // $assetTagRequired = $this->siteConfigRepository->findOneByName('asset_asset_tag_required') == "true";
        // $serialNumberRequired = $this->siteConfigRepository->findOneByName('asset_serial_number_required') == "true";

        $listener = function (FormEvent $event): void {
            $data = $event->getData();
            if (empty($data['asset_tag']) && empty($data['serial_number'])) {
                throw new TransformationFailedException(
                    'Asset Tag and/or Serial Number are required fields.',
                    0,
                    null,
                    'Either Asset Tag or Serial Number must be filled in.',
                );
            }
        };

        $builder
            ->add('asset_tag', TextType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('assigned_to', ChoiceType::class, [
                'choices' => array_combine(array_values($this->users), array_keys($this->users)),
                'required' => false,
                'multiple' => false,
                'attr' => [
                    'class' => 'form-control js-example-basic-single',
                ]
            ])
            ->add('userId', HiddenType::class)
            ->add('assetId', HiddenType::class)
            ->add('storageId', HiddenType::class)
            ->add('needs_repair', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off'
                ]
            ])
            ->add('location', HiddenType::class)
            ->add('serial_number', TextType::class, [
                'required' => false,
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
            ->add('check_out', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off'
                ],
            ])
            ->add('processed', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off'
                ],
            ])
            ->add('repairPartsNeeded', EntityType::class, [
                'class' => RepairParts::class,
                'expanded' => true,
                'multiple' => true,
                'choice_label' => 'name',
                'choice_attr' => function ($choice, string $key, mixed $value) {
                    return [
                        'class' => 'form-check-input',
                    ];
                },
            ])
            ->add('Collect', SubmitType::class)
            ->add('clearLocation', SubmitType::class)
            ->addEventListener(FormEvents::PRE_SUBMIT, $listener)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
