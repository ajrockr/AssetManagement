<?php

namespace App\Controller\Admin;

use App\Entity\SiteConfig;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class SiteConfigCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SiteConfig::class;
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
