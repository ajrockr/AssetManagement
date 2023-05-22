<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\SiteView;
use App\Entity\SiteConfig;
use App\Entity\AlertMessage;
use App\Entity\CustomUserField;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

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
        $visitorCount = $this->entityManager->getRepository(SiteView::class)->getCount();

        $lastCreatedUser = $this->entityManager->getRepository(User::class)->getLastCreatedUser();
        $lastCreatedUser = $lastCreatedUser['firstname'] . ' ' . $lastCreatedUser['surname'];

        return $this->render('admin/index.html.twig', [
            'userCount' => $userCount,
            'visitorCount' => $visitorCount,
            'lastCreatedUser' => $lastCreatedUser
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

            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),

            MenuItem::section('Site'),
            MenuItem::linkToRoute('Configuration', 'fa fa-gear', 'app_admin_site_config'),
            MenuItem::linkToRoute('Plugins', 'fa fa-plug', 'admin'),
            MenuItem::linkToCrud('Alert Message', 'fa fa-solid fa-message', AlertMessage::class),
            MenuItem::linkToCrud('Site Config (dev)', 'fa fa-home', SiteConfig::class),
            
            MenuItem::section('People'),
            MenuItem::subMenu('Users', 'fa fa-user')->setSubItems([
                MenuItem::linkToCrud('List Users', 'fa fa-home', User::class),
                MenuItem::linkToCrud('Custom Fields', 'fa fa-solid fa-address-card', CustomUserField::class)
            ])
        ];
    }
}
