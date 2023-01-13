<?php

namespace App\Controller\Admin;

use App\Entity\AlertMessage;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AlertMessageCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return AlertMessage::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('subject'),
            TextEditorField::new('message'),
            BooleanField::new('active'),
            TextField::new('source')->onlyOnIndex(),
            DateTimeField::new('dateCreated')->onlyOnIndex()
        ];
    }

    public function createEntity(string $entityFqcn)
    {
        $user = $this->getUser()->getUserIdentifier();
        $entity = new AlertMessage();
        $entity->setSource($user);
        $entity->setDateCreated(new \DateTimeImmutable());
        return $entity;
    }
}
