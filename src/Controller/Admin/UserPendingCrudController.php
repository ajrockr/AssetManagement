<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserPendingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {

        return [
            TextField::new('username')->onlyOnIndex(),
            TextField::new('email')->onlyOnIndex(),
            TextField::new('surname')->setRequired(true),
            TextField::new('firstname')->setRequired(true)
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_INDEX, 'View Pending Users')
            ->setHelp('index', 'View users pending admin approval.')
        ;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->where('entity.pending = true');
    }

    public function configureActions(Actions $actions): Actions
    {
        $approveAction = Action::new('Approve', null, 'fa fa-check')
            ->linkToCrudAction('approveAction')
            ->addCssClass('btn btn-success')
        ;

        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, $approveAction)
            ->disable(Action::EDIT, Action::NEW)
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn (Action $action) => $action->setLabel('Deny')->setIcon('fa fa-ban')->addCssClass('btn btn-secondary'))
        ;
    }

    public function approveAction(AdminContext $context)
    {
        $id = $context->getRequest()->query->get('entityId');
        $em = $this->container->get('doctrine')->getManager();
        $ur = $em->getRepository(User::class)->find($id)
            ->setPending(false)
            ->setEnabled(true);
        $this->persistEntity($em, $ur);

        $url = $this->container->get(AdminUrlGenerator::class)
                ->setController(UserPendingCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl();
        return $this->redirect($url);
    }
}
