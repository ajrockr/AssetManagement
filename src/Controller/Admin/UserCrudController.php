<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormEvents;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Symfony\Component\Form\FormBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add(BooleanFilter::new('pending'))
            ->add(BooleanFilter::new('enabled'))
            ->add('department')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $roles = ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER'];
        return [
            FormField::addPanel('User Data')->setIcon('fa fa-user'),
            TextField::new('username'),
            EmailField::new('email')->onlyWhenUpdating()->setDisabled(),
            EmailField::new('email')->onlyWhenCreating(),
            TextField::new('email')->onlyOnIndex(),
            ChoiceField::new('roles')
                ->setChoices(array_combine($roles, $roles))
                ->allowMultipleChoices()
                ->renderAsBadges(),
            BooleanField::new('pending'),
            BooleanField::new('enabled'),
            TextField::new('surname')->setRequired(true),
            TextField::new('firstname')->setRequired(true),
            TextField::new('manager')->onlyOnForms(),
            TextField::new('department')
                ->formatValue(function($value) {
                    return ($value === NULL) ? '' : $value;
                }),
            TextField::new('googleId')->onlyOnForms(),
            TextField::new('location')->onlyOnForms(),
            TextField::new('phone')->onlyOnForms(),
            TextField::new('extension')->onlyOnForms(),
            TextField::new('title')->onlyOnForms(),
            TextField::new('homepage')->onlyOnForms(),
            FormField::addPanel('Change Password')->setIcon('fa fa-key'),
            Field::new('password')
                ->setFormType(RepeatedType::class)
                ->setFormTypeOptions([
                    'type' => PasswordType::class,
                    'first_options' => ['label' => 'Password'],
                    'second_options' => ['label' => 'Repeat Password'],
                    'error_bubbling' => true,
                    'mapped' => false,
                    'invalid_message' => 'Passwords do not match'
                ])
                ->setRequired($pageName === Crud::PAGE_NEW)
                ->onlyOnForms(),
        ];
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        return $this->addPasswordEventListener($formBuilder);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        return $this->addPasswordEventListener($formBuilder);
    }

    private function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());
    }

    private function hashPassword()
    {
        return function($event) {
            $form = $event->getForm();
            if (!$form->isValid()) {
                return;
            }

            $password = $form->get('password')->getData();

            if ($password === null) {
                return;
            }

            $hash = $this->userPasswordHasher->hashPassword($this->getUser(), $password);
            $form->getData()->setPassword($hash);
        };
    }

    public function createEntity(string $entityFqcn)
    {
        $entity = new User();
        $entity->setDateCreated(new \DateTimeImmutable());
        return $entity;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_INDEX, 'View Users')
            ->setHelp('index', 'View the registered users.')
        ;
    }

    public function delete(AdminContext $adminContext)
    {
        // Prevent deleting super admin accounts.
        // @todo, Expand with custom role system eventually...
        $roles = $adminContext->getEntity()->getInstance()->getRoles();
        if (in_array('ROLE_SUPER_ADMIN', $roles)) {
            $url = $this->container->get(AdminUrlGenerator::class)
                ->setController(UserCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            $this->addFlash('warning', 'Can not delete this user account.');

            return new RedirectResponse($url);
        }
        return parent::delete($adminContext);
    }

    // public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    // {
    //     $qb = $this->get(UserRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
    //     $qb->andWhere('user.pending = :pending');
    //     $qb->setParameter('pending', true);

    //     return $qb;
    // }
}
