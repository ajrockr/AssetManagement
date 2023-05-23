<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class AdminSiteConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // dd($options);
        $builder
            // Company Information
            ->add('companyName', TextType::class, [
                'data' => $options['data']['company_name']
            ])
            ->add('companyMotto', TextType::class, [
                'data' => $options['data']['company_motto'],
                'required' => false
            ])
            ->add('companyAddress', TextType::class, [
                'data' => $options['data']['company_address']
            ])
            ->add('companyPhone', TextType::class, [
                'data' => $options['data']['company_phone'],
            ])
            ->add('companyFax', TextType::class, [
                'data' => $options['data']['company_fax'],
                'required' => false
            ])

            // Social Information
            ->add('companyFacebook', TextType::class, [
                'data' => $options['data']['social_facebook'],
                'required' => false
            ])
            ->add('companyTwitter', TextType::class, [
                'data' => $options['data']['social_twitter'],
                'required' => false
            ])
            ->add('companyYoutube', TextType::class, [
                'data' => $options['data']['social_youtube'],
                'required' => false
            ])
            ->add('companyInstagram', TextType::class, [
                'data' => $options['data']['social_instagram'],
                'required' => false
            ])
            ->add('useGoogleAuth', CheckboxType::class, [
                'data' => $options['data']['auth_useGoogle'] ? true : false,
                'help' => 'Client ID and Secret Key must be provided to Support. This information is hard coded into the application for security.',
                'required' => false
            ])

            // Authentication Information
            ->add('useMicrosoftAuth', CheckboxType::class, [
                'data' => $options['data']['auth_useMicrosoft'] ? true : false,
                'help' => 'Client ID and Secret Key must be provided to Support. This information is hard coded into the application for security.',
                'required' => false
            ])
            ->add('useNoExternalAuth', CheckboxType::class, [
                'data' => $options['data']['auth_useMicrosoft'] <=> $options['data']['auth_useGoogle'] ? false : true,
                'required' => false
            ])
            // Prefer this, but having trouble with styling
            // ->add('useAuth', ChoiceType::class, [
            //     'choice_attr' => function ($choice, $key, $value) {
            //         return ['class' => 'form-check-input'];
            //     },
            //     'choices' => [
            //         'Google Authentication' => 'useGoogleAuth',
            //         'Microsoft Authentication' => 'useMicrosoftAuth'
            //     ],
            //     'expanded' => true,
            //     'multiple' => false,
            //     'placeholder' => false,
            //     'required' => false
            // ])

            // User Profiles
            ->add('allowUserEditProfile', CheckboxType::class, [
                'data' => $options['data']['profile_allowUserEditing'] ? true : false,
                'required' => false
            ])
            ->add('allowManagerEditProfile', CheckboxType::class, [
                'data' => $options['data']['profile_allowManagerEditing'] ? true : false,
                'required' => false
            ])
            // @todo, finish rest of profile stuff

            // Site Information
            ->add('setMaintenanceModeEnabled', CheckboxType::class, [
                'data' => $options['data']['site_maintenanceModeEnabled'] ? true : false,
                'required' => false
            ])
            ->add('setAlertMessageEnabled', CheckboxType::class, [
                'data' => $options['data']['site_alertMessageEnabled'] ? true : false,
                'required' => false
            ])
            ->add('setAllowUserRegistration', CheckboxType::class, [
                'data' => $options['data']['user_allowRegistration'] ? true : false,
                'required' => false
            ])

            ->add('save', SubmitType::class, [
                'label' => 'Save Changes',
                'attr' => ['class' => 'save']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
