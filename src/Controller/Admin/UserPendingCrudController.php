<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
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
            TextField::new('firstname')->setRequired(true),
            TextField::new('manager')->onlyOnForms(),
            TextField::new('department')
                ->formatValue(function($value) {
                    return ($value === NULL) ? '' : $value;
                })
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
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $approveAction)
            ->disable('edit')
            ->disable('delete')
        ;
    }

    public function approveAction(AdminContext $context)
    {
        $id = $context->getRequest()->query->get('entityId');
        $entity = $this->doctrine->getRepository(User::class)->find(['id' => $id]);
        dd($entity);
    }
}
