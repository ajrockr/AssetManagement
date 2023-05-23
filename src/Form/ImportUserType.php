<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ImportUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('fileCsv', FileType::class, [
                'attr' => [
                    'class' => 'form-control m-2',
                ],
                'label' => 'Import Users (csv)',
                'label_attr' => [
                    'class' => 'form-label display-6'
                ],
                'help' => 
                    'Get-ADUser -SearchBase "OU=Students,OU=Accounts,DC=WESTEX,DC=ORG" -Filter {Mail -Like \'*\'} -Properties Description,SamAccountName,Mail,GivenName,Surname | Select Description,SamAccountName,Mail,GivenName,Surname | Export-Csv C:/choose/a/path/file.csv -NoTypeInformation -NoClobber',
                'help_attr' => [
                    'class' => 'form-text text-muted',
                ],
                'required' => true,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'text/csv'
                        ]
                    ])
                ],
            ])
            ->add('upload', SubmitType::class, [
                'attr' => [
                    'class' => 'form-control btn btn-primary m-2'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
