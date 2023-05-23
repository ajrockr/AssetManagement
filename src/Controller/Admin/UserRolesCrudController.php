<?php

namespace App\Controller\Admin;

use App\Entity\UserRoles;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserRolesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserRoles::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
