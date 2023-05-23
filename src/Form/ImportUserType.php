<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('roles')
            ->add('password')
            ->add('email')
            ->add('location')
            ->add('department')
            ->add('phone')
            ->add('extension')
            ->add('title')
            ->add('homepage')
            ->add('manager')
            ->add('googleId')
            ->add('microsoftId')
            ->add('dateCreated')
            ->add('surname')
            ->add('firstname')
            ->add('enabled')
            ->add('pending')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
