<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\UserRoles;
use Symfony\Component\Form\FormEvents;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Symfony\Component\Form\FormBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
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
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AvatarField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_SUPER_ADMIN')]
#[IsGranted('ROLE_USER_ADMIN')]
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
        // Get roles
        $roles = $this->getRoles();

        return [
            // Account Data Tab
            FormField::addTab('Account Data')->setIcon('fa fa-user'),
            AvatarField::new('avatar')
                ->onlyOnIndex()
                ->formatValue(function($value, $entity) {
                    return ($entity->getAvatar()) ? $value : 'https://upload.wikimedia.org/wikipedia/commons/2/2c/Default_pfp.svg';
                }),
            TextField::new('username'),
            EmailField::new('email')->onlyWhenUpdating()->setDisabled(),
            EmailField::new('email')->onlyWhenCreating(),
            TextField::new('email')->onlyOnIndex(),
            TextField::new('type'),
            TextField::new('userUniqueId'),
            ChoiceField::new('roles')
                ->setChoices(array_combine(array_keys($roles), array_values($roles)))
                ->allowMultipleChoices()
                ->renderAsBadges(),
            BooleanField::new('pending')
                ->onlyOnIndex()
                ->renderAsSwitch(false),
            BooleanField::new('enabled')
                ->onlyOnIndex()
                ->renderAsSwitch(false),
            TextField::new('googleId')
                ->onlyOnForms()
                ->setFormTypeOptions(['attr' => ['value' => '********']])
                ->setDisabled(),
            TextField::new('microsoftId')
                ->onlyOnForms()
                ->setFormTypeOptions(['attr' => ['value' => '********']])
                ->setDisabled(),

            // User Information Tab
            FormField::addTab('User Information')->setIcon('fa fa-id-card'),
            TextField::new('surname')->setRequired(true),
            TextField::new('firstname')->setRequired(true),
            TextField::new('manager')->onlyOnForms(),
            TextField::new('department')
                ->formatValue(function($value) {
                    return ($value === NULL) ? '' : $value;
                }),
            TextField::new('location')->onlyOnForms(),
            TextField::new('phone')->onlyOnForms(),
            TextField::new('extension')->onlyOnForms(),
            TextField::new('title')->onlyOnForms(),
            TextField::new('homepage')->onlyOnForms(),

            // Change Password Tab
            FormField::addTab('Change Password')->setIcon('fa fa-key'),
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
                ->onlyOnForms()
        ];
    }

    public function getPendingField(AdminContext $context)
    {
        $id = $context->getRequest()->query->get('entityId');
        $em = $this->container->get('doctrine')->getManager();
        $ur = $em->getRepository(User::class)->find($id)
            ->isPending();
        dd($ur);
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
        $disableUserAction = Action::new('Disable')
            ->linkToCrudAction('disableUserAction')
        ;

        $enableUserAction = Action::new('Enable')
            ->linkToCrudAction('enableUserAction')
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, $disableUserAction)
            ->add(Crud::PAGE_INDEX, $enableUserAction)
            ->setPermission($disableUserAction, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::NEW, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_SUPER_ADMIN')
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // ->showEntityActionsInlined()
            ->setPageTitle(Crud::PAGE_INDEX, 'View Users')
            ->setHelp('index', 'View the registered users.')
            ->setDefaultSort(['id' => 'ASC'])
        ;
    }

    public function delete(AdminContext $context)
    {
        // Prevent deleting super admin accounts.
        // @todo, Expand with custom role system eventually...
        $roles = $context->getEntity()->getInstance()->getRoles();
        if (in_array('ROLE_SUPER_ADMIN', $roles)) {
            $url = $this->container->get(AdminUrlGenerator::class)
                ->setController(UserCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl();

            $this->addFlash('warning', 'Can not delete this user account.');

            return new RedirectResponse($url);
        }
        return parent::delete($context);
    }

    public function disableUserAction(AdminContext $context)
    {
        $id = $context->getRequest()->query->get('entityId');
        $em = $this->container->get('doctrine')->getManager();
        $ur = $em->getRepository(User::class)->find($id)
            ->setEnabled(false);
        $this->persistEntity($em, $ur);

        $url = $this->container->get(AdminUrlGenerator::class)
                ->setController(UserCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl();
        return $this->redirect($url);
    }

    public function enableUserAction(AdminContext $context)
    {
        $id = $context->getRequest()->query->get('entityId');
        $em = $this->container->get('doctrine')->getManager();
        $ur = $em->getRepository(User::class)->find($id)
            ->setEnabled(true);
        $this->persistEntity($em, $ur);

        $url = $this->container->get(AdminUrlGenerator::class)
                ->setController(UserCrudController::class)
                ->setAction(Action::INDEX)
                ->generateUrl();
        return $this->redirect($url);
    }

    public function getRoles(): array
    {
        $return = [];
        $em = $this->container->get('doctrine')->getManager();
        $roles = $em->getRepository(UserRoles::class)->findAll();
        foreach($roles as $role) {
            $return[$role->getRoleName()] = $role->getRoleValue();
        }

        return $return;
    }
}
