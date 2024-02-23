<?php

namespace App\Controller\Install;

use App\Entity\SiteConfig;
use App\Entity\User;
use App\Entity\UserRoles;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class InstallController extends AbstractController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) { }

    #[Route('/install', name: 'app_install')]
    public function index(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('company_name', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control mb-2'
                ]
            ])
            ->add('admin_username', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control mb-2'
                ]
            ])
            ->add('admin_email', EmailType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control mb-2'
                ]
            ])
            ->add('admin_firstname', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control mb-2'
                ]
            ])
            ->add('admin_surname', TextType::class, [
                'required' => true,
                'attr' => [
                    'class' => 'form-control mb-2'
                ]
            ])
            ->add('admin_password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The passwords must match.',
                'options' => [
                    'attr' => [
                        'class' => 'form-control mb-2'
                    ]
                ],
                'required' => true,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password']
            ])
            ->add('install', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->createAdminUser($data['admin_username'], $data['admin_email'], $data['admin_password'], $data['admin_firstname'], $data['admin_surname']);
            $this->createConfig($data['company_name']);
            $this->createUserRoles();

            // Next delete self
        }

        return $this->render('install/index.html.twig', [
            'install_form' => $form->createView(),
        ]);
    }

    private function createAdminUser(string $username, string $email, string $password, string $firstname, string $surname): void
    {
        $this->entityManager->createQuery(
            'TRUNCATE App\Entity\User'
        )->execute();

        $user = new User();
        $user->setUsername($username)
            ->setEmail($email)
            ->setFirstname($firstname)
            ->setSurname($surname)
            ->setPending(false)
            ->setEnabled(true)
            ->setRoles(['ROLE_SUPER_ADMIN'])
            ->setPassword($this->userPasswordHasher->hashPassword($user, $password));

        $this->userRepository->save($user, true);
    }

    private function createUserRoles(): void
    {
        $this->entityManager->createQuery(
            'TRUNCATE App\Entity\UserRoles'
        )->execute();

        $userRoles = [
            [
                'name' => 'User',
                'value' => 'ROLE_USER',
                'description' => 'Basic authenticated user.'
            ],
            [
                'name' => 'Admin',
                'value' => 'ROLE_ADMIN',
                'description' => '	Allow access to the admin dashboard.'
            ],
            [
                'name' => 'Super Admin',
                'value' => 'ROLE_SUPER_ADMIN',
                'description' => 'Allow access to every administrative option.'
            ],
            [
                'name' => 'User Admin',
                'value' => 'ROLE_USER_ADMIN',
                'description' => 'Allow ability to manage users.'
            ],
            [
                'name' => 'Deny Login',
                'value' => 'ROLE_DENY_LOGIN',
                'description' => 'Deny the user from logging into the application.'
            ],
            [
                'name' => 'Asset Read',
                'value' => 'ROLE_ASSET_READ',
                'description' => 'Can view asset data.'
            ],
            [
                'name' => 'Asset Modify',
                'value' => 'ROLE_ASSET_MODIFY',
                'description' => 'Can modify asset data.'
            ],
            [
                'name' => 'Asset Full Control',
                'value' => 'ROLE_ASSET_FULL_CONTROL',
                'description' => 'Has full control over asset data.'
            ],
            [
                'name' => 'Repair Technician',
                'value' => 'ROLE_REPAIR_TECHNICIAN',
                'description' => 'User can perform repairs on assets.'
            ],
            [
                'name' => 'Repair Read',
                'value' => 'ROLE_ASSET_REPAIR_READ',
                'description' => 'Can view repair data.'
            ],
            [
                'name' => 'Repair Modify',
                'value' => 'ROLE_ASSET_REPAIR_MODIFY',
                'description' => 'Can modify repair data.'
            ],
            [
                'name' => 'Repair Full Control',
                'value' => 'ROLE_ASSET_REPAIR_FULL_CONTROL',
                'description' => 'Has full control over repair data.'
            ],
            [
                'name' => 'Report Viewer',
                'value' => 'ROLE_REPORT_VIEWER',
                'description' => 'Can view report data.'
            ],
        ];

        foreach ($userRoles as $role) {
            $r = new UserRoles();
            $r->setRoleName($role['name']);
            $r->setRoleValue($role['value']);
            $r->setRoleDescription($role['description']);
            $this->entityManager->persist($r);
        }

        $this->entityManager->flush();
    }

    private function createConfig(string $companyName): void
    {
        $this->entityManager->createQuery(
            'TRUNCATE App\Entity\SiteConfig'
        )->execute();

        $siteConfig = [
            [
                'name' => 'auth_useGoogle',
                'value' => 1,
                'description' => 'Use Google to authenticate users.',
                'default_value' => 0
            ],
            [
                'name' => 'auth_useMicrosoft',
                'value' => 0,
                'description' => 'Use Microsoft to authenticate users.',
                'default_value' => 0
            ],
            [
                'name' => 'company_name',
                'value' => $companyName,
                'description' => 'Name of your company.',
                'default_value' => null
            ],
            [
                'name' => 'company_motto',
                'value' => null,
                'description' => 'Company motto.',
                'default_value' => null
            ],
            [
                'name' => 'company_address',
                'value' => null,
                'description' => 'Address of the company.',
                'default_value' => null
            ],
            [
                'name' => 'company_phone',
                'value' => null,
                'description' => 'Company phone number.',
                'default_value' => null
            ],
            [
                'name' => 'company_fax',
                'value' => null,
                'description' => 'Company fax line.',
                'default_value' => null
            ],
            [
                'name' => 'social_facebook',
                'value' => null,
                'description' => 'Display a Facebook page.',
                'default_value' => null
            ],
            [
                'name' => 'social_twitter',
                'value' => null,
                'description' => 'Display a Twitter page.',
                'default_value' => null
            ],
            [
                'name' => 'social_youtube',
                'value' => null,
                'description' => 'Display a YouTube page.',
                'default_value' => null
            ],
            [
                'name' => 'social_instagram',
                'value' => null,
                'description' => 'Display a Instagram page.',
                'default_value' => null
            ],
            [
                'name' => 'profile_allowUserEditing',
                'value' => 1,
                'description' => 'Allow users to edit their own profile page.',
                'default_value' => 1
            ],
            [
                'name' => 'profile_allowManagerEditing',
                'value' => 0,
                'description' => 'Allow a manger to edit other users profile fields.',
                'default_value' => 0
            ],
            [
                'name' => 'site_maintenanceModeEnabled',
                'value' => 0,
                'description' => 'Set the site to maintenance mode which will disable all but administrators from using it.',
                'default_value' => 0
            ],
            [
                'name' => 'site_alertMessageEnabled',
                'value' => 0,
                'description' => 'Enable the Alert Message system.',
                'default_value' => 0
            ],
            [
                'name' => 'user_allowRegistration',
                'value' => 1,
                'description' => 'Allow users to register during initial login attempt.',
                'default_value' => 1
            ],
            [
                'name' => 'asset_assignUser_on_checkin',
                'value' => 0,
                'description' => 'Will overwrite an assigned user when the asset is checked in.',
                'default_value' => 0
            ],
            [
                'name' => 'asset_unique_identifier',
                'value' => 'asset_tag',
                'description' => 'The unique identifier that is used to identify assets in various pages.',
                'default_value' => 'asset_tag'
            ],
            [
                'name' => 'collection_color_cell_occupied',
                'value' => '#80c0ed',
                'description' => 'The cell color to represent when occupied with an asset.',
                'default_value' => '#80c0ed'
            ],
            [
                'name' => 'collection_color_cell_checkedout',
                'value' => '#ff0000',
                'description' => 'The cell color to represent when occupied asset is checked out.',
                'default_value' => '#ff0000'
            ],
            [
                'name' => 'collection_color_cell_processed',
                'value' => '#4287f5',
                'description' => 'The cell color to represent when occupied asset has been processed by technician.',
                'default_value' => '#4287f5'
            ],
            [
                'name' => 'collection_color_cell_hasrepair',
                'value' => '#ff9c9c',
                'description' => 'The cell color to represent when occupied asset has a repair associated with it.',
                'default_value' => '#ff9c9c'
            ],
            [
                'name' => 'asset_asset_tag_required',
                'value' => 'true',
                'description' => 'Set the Asset Tag to be a required field.',
                'default_value' => 'true'
            ],
            [
                'name' => 'asset_serial_number_required',
                'value' => 'false',
                'description' => 'Set the Serial Number to be a required field.',
                'default_value' => 'false'
            ],
        ];

        foreach ($siteConfig as $configItem) {
            $config = new SiteConfig();
            $config->setConfigName($configItem['name']);
            $config->setConfigValue($configItem['value']);
            $config->setConfigDescription($configItem['description']);
            $config->setDefaultValue($configItem['default_value']);
            $this->entityManager->persist($config);
        }

        $this->entityManager->flush();
    }

    /**
     * Deletes this file and the /install/ directory
     * @return void
     */
    private function deleteSelf(): void
    {
        unlink(__FILE__);
        $dir = dirname(__FILE__);
        if (is_dir($dir) && $this->isDirEmpty($dir)) {
            rmdir($dir);
        }

    }

    /**
     * @param string $dir
     * @return bool|null
     */
    private function isDirEmpty(string $dir): ?bool
    {
        if ( !is_readable($dir)) return null;
        return (count(scandir($dir)) == 2);
    }
}
