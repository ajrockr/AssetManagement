<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
                'data' => (bool)$options['data']['auth_useGoogle'],
                'help' => 'Client ID and Secret Key must be provided to Support. This information is hard coded into the application for security.',
                'required' => false
            ])

            // Authentication Information
            ->add('useMicrosoftAuth', CheckboxType::class, [
                'data' => (bool)$options['data']['auth_useMicrosoft'],
                'help' => 'Client ID and Secret Key must be provided to Support. This information is hard coded into the application for security.',
                'required' => false
            ])
            ->add('useNoExternalAuth', CheckboxType::class, [
                'data' => !($options['data']['auth_useMicrosoft'] <=> $options['data']['auth_useGoogle']),
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
                'data' => (bool)$options['data']['profile_allowUserEditing'],
                'required' => false
            ])
            ->add('allowManagerEditProfile', CheckboxType::class, [
                'data' => (bool)$options['data']['profile_allowManagerEditing'],
                'required' => false
            ])
            // @todo, finish rest of profile stuff

            // Site Information
            ->add('setMaintenanceModeEnabled', CheckboxType::class, [
                'data' => (bool)$options['data']['site_maintenanceModeEnabled'],
                'required' => false
            ])
            ->add('setAlertMessageEnabled', CheckboxType::class, [
                'data' => (bool)$options['data']['site_alertMessageEnabled'],
                'required' => false
            ])
            ->add('setAllowUserRegistration', CheckboxType::class, [
                'data' => (bool)$options['data']['user_allowRegistration'],
                'required' => false
            ])
            ->add('setDeviceUniqueId', TextType::class, [
                'data' => $options['data']['asset_unique_identifier'],
                'required' => true
            ])
            ->add('setAssignUserOnCheckIn', CheckboxType::class, [
                'data' => (bool)$options['data']['asset_assignUser_on_checkin'],
                'required' => false
            ])
            ->add('setCollectionCelColorOccupied', TextType::class, [
                'data' => $options['data']['collection_color_cell_occupied'],
            ])
            ->add('setCollectionCelColorCheckedOut', TextType::class, [
                'data' => $options['data']['collection_color_cell_checkedout'],
            ])
            ->add('setCollectionCelColorProcessed', TextType::class, [
                'data' => $options['data']['collection_color_cell_processed'],
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
