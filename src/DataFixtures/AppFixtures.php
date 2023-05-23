<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\SiteConfig;
use App\Entity\UserRoles;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Create the configuration items that the application needs
        // @todo Update this as needed
        $siteConfig = [
            [
                'name' => 'auth_useGoogle',
                'value' => 1,
                'description' => 'Use Google to authenticate users.',
                'default_value' => 0,
            ],
            [
                'name' => 'auth_useMicrosoft',
                'value' => 0,
                'description' => 'Use Microsoft to authenticate users.',
                'default_value' => 0,
            ],
            [
                'name' => 'company_name',
                'value' => 'Test Company',
                'description' => 'Name of your company.',
                'default_value' => null,
            ],
            [
                'name' => 'company_motto',
                'value' => null,
                'description' => 'Company motto.',
                'default_value' => null,
            ],
            [
                'name' => 'company_address',
                'value' => null,
                'description' => 'Address of the company.',
                'default_value' => null,
            ],
            [
                'name' => 'company_phone',
                'value' => null,
                'description' => 'Company phone number.',
                'default_value' => null,
            ],
            [
                'name' => 'company_fax',
                'value' => null,
                'description' => 'Company fax line.',
                'default_value' => null,
            ],
            [
                'name' => 'social_facebook',
                'value' => null,
                'description' => 'Display a Facebook page.',
                'default_value' => null,
            ],
            [
                'name' => 'social_twitter',
                'value' => null,
                'description' => 'Display a Twitter page.',
                'default_value' => null,
            ],
            [
                'name' => 'social_youtube',
                'value' => null,
                'description' => 'Display a YouTube page.',
                'default_value' => null,
            ],
            [
                'name' => 'social_instagram',
                'value' => null,
                'description' => 'Display a Instagram page.',
                'default_value' => null,
            ],
            [
                'name' => 'profile_allowUserEditing',
                'value' => 1,
                'description' => 'Allow users to edit their own profile page.',
                'default_value' => 1,
            ],
            [
                'name' => 'profile_allowManagerEditing',
                'value' => 0,
                'description' => 'Allow a manger to edit other users profile fields.',
                'default_value' => 0,
            ],
            [
                'name' => 'site_maintenanceModeEnabled',
                'value' => 0,
                'description' => 'Set the site to maintenance mode which will disable all but administrators from using it.',
                'default_value' => 0,
            ],
            [
                'name' => 'site_alertMessageEnabled',
                'value' => 0,
                'description' => 'Enable the Alert Message system.',
                'default_value' => 0,
            ],
            [
                'name' => 'user_allowRegistration',
                'value' => 1,
                'description' => 'Allow users to register during initial login attempt.',
                'default_value' => 1,
            ],
        ];

        foreach ($siteConfig as $configItem) {
            $config = new SiteConfig();
            $config->setConfigName($configItem['name']);
            $config->setConfigValue($configItem['value']);
            $config->setConfigDescription($configItem['description']);
            $config->setDefaultValue($configItem['default_value']);
            $manager->persist($config);
        }

        // Create the built-in default admin account
        $user = new User();
        $user->setUsername('Admin');
        $user->setEmail('admin@admin.com');
        $user->setPending(false);
        $user->setEnabled(true);
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'changeme'));
        $manager->persist($user);

        // User roles
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
                'description' => '	Allow ability to manage users.'
            ],
        ];

        foreach ($userRoles as $role) {
            $r = new UserRoles();
            $r->setRoleName($role['name']);
            $r->setRoleValue($role['value']);
            $r->setRoleDescription($role['description']);
            $manager->persist($r);
        }
        
        // Flush the database
        $manager->flush();
    }
}
