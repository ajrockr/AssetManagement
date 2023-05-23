<?php

namespace App\Controller\Admin;

use App\Entity\SiteConfig;
use App\Form\AdminSiteConfigType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SiteConfigController extends AbstractController
{
    #[Route('/admin/site/config', name: 'app_admin_site_config')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $currentConfig = $entityManager->getRepository(SiteConfig::class)->getAllConfigItems();
        $form = $this->createForm(AdminSiteConfigType::class, $currentConfig);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            // dd($data);
            
            // Company Information
            $this->updateConfigItem($entityManager, 'company_name', $data['companyName']);
            $this->updateConfigItem($entityManager, 'company_motto', $data['companyMotto']);
            $this->updateConfigItem($entityManager, 'company_address', $data['companyAddress']);
            $this->updateConfigItem($entityManager, 'company_phone', $data['companyPhone']);
            $this->updateConfigItem($entityManager, 'company_fax', $data['companyFax']);

            // Social Information
            $this->updateConfigItem($entityManager, 'social_facebook', $data['companyFacebook']);
            $this->updateConfigItem($entityManager, 'social_twitter', $data['companyTwitter']);
            $this->updateConfigItem($entityManager, 'social_youtube', $data['companyYoutube']);
            $this->updateConfigItem($entityManager, 'social_instagram', $data['companyInstagram']);

            // Authentication Information
            $this->updateConfigItem($entityManager, 'auth_useGoogle', $data['useGoogleAuth']);
            $this->updateConfigItem($entityManager, 'auth_useMicrosoft', $data['useMicrosoftAuth']);

            // Profile Information
            $this->updateConfigItem($entityManager, 'profile_allowUserEditing', $data['allowUserEditProfile']);
            $this->updateConfigItem($entityManager, 'profile_allowManagerEditing', $data['allowManagerEditProfile']);
            
            // Site Information
            $this->updateConfigItem($entityManager, 'site_maintenanceModeEnabled', $data['setMaintenanceModeEnabled']);
            $this->updateConfigItem($entityManager, 'site_alertMessageEnabled', $data['setAlertMessageEnabled']);
            $this->updateConfigItem($entityManger, 'user_allowRegistration', $data['setAllowUserRegistration']);
        }

        return $this->render('admin/site_config/index.html.twig', [
            'adminSiteConfig' => $form,
        ]);
    }

    private function updateConfigItem(EntityManagerInterface $entityManager, string $configName, mixed $configValue): void
    {
        $config = $entityManager->getRepository(SiteConfig::class)->findOneByName($configName);
        $config->setConfigValue($configValue);
        $entityManager->flush();
    }
}
