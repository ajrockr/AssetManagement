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
use EasyCorp\Bundle\EasyAdminBundle\Field\AvatarField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]
#[IsGranted('ROLE_USER_ADMIN')]
class UserDisabledCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {

        return [
            AvatarField::new('avatar')
            ->onlyOnIndex()
            ->formatValue(function($value, $entity) {
                return ($entity->getAvatar()) ? $value : 'https://upload.wikimedia.org/wikipedia/commons/2/2c/Default_pfp.svg';
            }),
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
            ->setPageTitle(Crud::PAGE_INDEX, 'View Disabled Users')
            ->setHelp('index', 'View users that have been administratively disabled.')
        ;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->where('entity.enabled = false')
            ->andWhere('entity.pending = false');
    }

    public function configureActions(Actions $actions): Actions
    {
        $enableUserAction = Action::new('Enable', null, 'fa fa-check')
            ->linkToCrudAction('enableUserAction')
            ->addCssClass('btn btn-success')
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $enableUserAction)
            ->setPermission($enableUserAction, 'ROLE_SUPER_ADMIN')
            ->disable(Action::EDIT, Action::NEW, Action::DELETE)
        ;
    }

    public function enableUserAction(AdminContext $context)
    {
        $id = $context->getRequest()->query->get('entityId');
        $em = $this->container->get('doctrine')->getManager();
        $ur = $em->getRepository(User::class)->find($id)
            ->setEnabled(true);
        $this->persistEntity($em, $ur);

        $url = $this->container->get(AdminUrlGenerator::class)
                ->setController(UserDisabledCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl();
        return $this->redirect($url);
    }
}
