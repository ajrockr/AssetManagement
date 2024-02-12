<?php

namespace App\Form;

use App\Entity\Repair;
use App\Entity\RepairParts;
use App\Entity\User;
use App\Repository\RepairPartsRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepairType extends AbstractType
{
    private array $repairParts = [];
    public function __construct(
        private readonly RepairPartsRepository $repairPartsRepository,
        private readonly UserRepository $userRepository
    )
    {
        foreach ($this->repairPartsRepository->findAll() as $part) {
            $this->repairParts[$part->getName()] = $part->getId();
        }
    }

    private function getValidTechnicians()
    {
        $returnArray = [];
        foreach ($this->userRepository->findAll() as $user) {
            if (in_array(['ROLE_REPAIR_TECHNICIAN', 'ROLE_SUPER_ADMIN'], $user->getRoles())) {
                $returnArray[] = $user;
            }
        }
        return $returnArray;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('assetId', HiddenType::class)
            ->add('assetUniqueIdentifier', TextType::class, [
                'label' => 'Asset'
            ])
            ->add('techId', EntityType::class, [
                'label' => 'Technician',
                'required' => false,
                'class' => User::class,
                'choice_label' => 'username',
                'choices' => $this->getValidTechnicians()
            ])
            ->add('issue', TextareaType::class, [
                'attr' => [
                    'rows' => 5,
                    'class' => 'tinymce'
                ]
            ])
            ->add('actionsPerformed')
            ->add('status', ChoiceType::class, [
                // TODO: Make this based off config
                'choices' => [
                    'Not Started' => Repair::STATUS_NOT_STARTED,
                    'In Progress' => Repair::STATUS_IN_PROGRESS,
                    'Deferred' => Repair::STATUS_DEFERRED,
                    'Waiting On Parts' => Repair::STATUS_WAITING_ON_PARTS,
                    'Waiting On Technician' => Repair::STATUS_WAITING_ON_TECHNICIAN,
                    'Waiting On User' => Repair::STATUS_WAITING_ON_USER,
                    'Resolved' => Repair::STATUS_CLOSED,
                    'Open' => Repair::STATUS_OPEN,
                ]
            ])
            ->add('usersFollowing')
        ;

        if (sizeof($this->repairParts) === 0) {
            $builder
                ->add('partsNeeded', TextType::class, [
                    'required' => false,
                    'disabled' => true,
                    'attr' => [
                        'class' => 'disabled',
                        'value' => 'No parts available'
                    ],
                    'empty_data' => []
                ])
            ;
        } else {
            $builder
                ->add('partsNeeded', ChoiceType::class, [
                    'required' => false,
                    'placeholder' => 'No parts available',
                    'choices' => [
                        $this->repairParts
                    ],
                    'choice_attr' => function ($opt, $k, $v) {
                        if ($k == $opt) {
                            return ['checked' => 'checked'];
                        }
                        return [];
                    },
                    'multiple' => true,
                    'expanded' => true,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Repair::class,
        ]);
    }
}
