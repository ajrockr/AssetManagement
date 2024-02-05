<?php

namespace App\Controller\Admin;

use App\Entity\AlertMessage;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN', statusCode: 423)]
class AlertMessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AlertMessage::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('subject'),
            TextareaField::new('message'),
            BooleanField::new('active'),
            TextField::new('source')->onlyOnIndex(),
            DateTimeField::new('dateCreated')->onlyOnIndex()
        ];
    }

    public function createEntity(string $entityFqcn): AlertMessage
    {
        $user = $this->getUser()->getUserIdentifier();
        $entity = new AlertMessage();
        $entity->setSource($user);
        $entity->setDateCreated(new \DateTimeImmutable());
        return $entity;
    }
}
