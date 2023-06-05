<?php

namespace App\Controller\Admin;

use App\Entity\RepairParts;
use App\Entity\Vendor;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CurrencyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use phpDocumentor\Reflection\Types\Integer;

class RepairPartsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RepairParts::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $vendors = $this->getVendors();
        return [
            TextField::new('name'),
            TextField::new('description'),
            ChoiceField::new('vendor')
                ->setChoices(array_combine(array_values($vendors), array_keys($vendors))),
            MoneyField::new('cost')->setCurrency('USD')
        ];
    }

    public function getVendors(): array
    {
        $return = [];
        $em = $this->container->get('doctrine')->getManager();
        $vendors = $em->getRepository(Vendor::class)->findAll();
        foreach($vendors as $vendor) {
            $return[$vendor->getId()] = $vendor->getName();
        }

        return $return;
    }
}
