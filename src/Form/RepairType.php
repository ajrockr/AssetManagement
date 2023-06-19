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
            if (in_array('ROLE_REPAIR_TECHNICIAN', $user->getRoles())) {
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
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Asset'
            ])
            ->add('techId', EntityType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Select a Technician'
                ],
                'class' => User::class,
                'choice_label' => 'username',
                'choices' => $this->getValidTechnicians()
            ])
            ->add('issue', TextareaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5
                ]
            ])
            ->add('partsNeeded', ChoiceType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'choices' => [
                    $this->repairParts
                ],
                'choice_attr' => function($opt, $k, $v) {
                    if ($k == $opt) {
                        return ['checked' => 'checked'];
                    }
                    return [];
                },
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('actionsPerformed')
            ->add('status', ChoiceType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Select Parts'
                ],
                // TODO: Make this based off config
                'choices' => [
                    'Not Started' => 'status_nostart',
                    'Started' => 'status_started',
                    'In Progress' => 'status_inprogress',
                    'Delayed' => 'status_delayed',
                    'Waiting On Parts' => 'status_waitingonparts',
                    'Waiting On Technician' => 'status_waitingontechnician',
                    'Waiting On Customer' => 'status_waitingoncustomer',
                    'Resolved' => 'status_resolved'
                ]
            ])
            ->add('usersFollowing')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Repair::class,
        ]);
    }
}
