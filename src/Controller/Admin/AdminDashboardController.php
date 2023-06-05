<?php

namespace App\Controller\Admin;

use App\Entity\Asset;
use App\Entity\AssetCollection;
use App\Entity\RepairParts;
use App\Entity\User;
use App\Entity\UserRoles;
use App\Entity\SiteConfig;
use App\Entity\AlertMessage;
use App\Entity\Vendor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

#[Security("is_granted('ROLE_ADMIN')")]
class AdminDashboardController extends AbstractDashboardController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $userCount = $this->entityManager->getRepository(User::class)->getUserCount();

        // @todo This is a very basic subscriber counting user hits. Production should probably be a little more robust
        $assetCount = count($this->entityManager->getRepository(Asset::class)->findAll());

        $assetsCollectedCount = count($this->entityManager->getRepository(AssetCollection::class)->findAll());

        $pendingUsers = count($this->entityManager->getRepository(User::class)->findBy(['pending' => true]));

        return $this->render('admin/index.html.twig', [
            'userCount' => $userCount,
            'assetCount' => $assetCount,
            'assetsCollectedCount' => $assetsCollectedCount,
            'pendingUserCount' => $pendingUsers
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Admin Dashboard')
            ->renderContentMaximized()
            ->disableDarkMode()
        ;
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('css/admin.css')->addJSFile('js/jquery.js')->addJSFile('js/admin.js');
    }

    public function configureMenuItems(): iterable
    {
        return [

            MenuItem::linkToRoute('Back to site', 'fa fa-house', 'app_home'),

            MenuItem::section('Site'),
            MenuItem::linkToRoute('Configuration', 'fa fa-gear', 'app_admin_site_config'),
            MenuItem::linkToRoute('Plugins', 'fa fa-plug', 'admin'),
            MenuItem::linkToCrud('Alert Message', 'fa fa-solid fa-message', AlertMessage::class),
            MenuItem::subMenu('Users', 'fa fa-users')->setSubItems([
                MenuItem::linkToCrud('List Users', 'fa fa-users-line', User::class)
                    ->setController(UserCrudController::class),
                MenuItem::linkToCrud('Pending Users', 'fa fa-user-shield', User::class)
                    ->setController(UserPendingCrudController::class),
                MenuItem::linkToCrud('Disabled Users', 'fa fa-users-slash', User::class)
                    ->setController(UserDisabledCrudController::class),
                MenuItem::linkToRoute('Import Users', 'fa fa-user-plus', 'app_admin_import_user')
            ]),

            MenuItem::section('Repairs'),
            MenuItem::linkToCrud('Repair Parts', 'fa fa-wrench', RepairParts::class),
            MenuItem::linkToCrud('Vendors', 'fa fa-building', Vendor::class),

            MenuItem::section('DEV'),
            MenuItem::linkToCrud('User Roles (dev)', 'fa fa-home', UserRoles::class),
            MenuItem::linkToCrud('Site Config (dev)', 'fa fa-home', SiteConfig::class),
        ];
    }
}
