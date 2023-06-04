<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AssetDistributionController extends AbstractController
{
    #[Route('/asset/distribution', name: 'app_asset_distribution')]
    public function index(): Response
    {
        return $this->render('asset_distribution/index.html.twig', [
            'controller_name' => 'AssetDistributionController',
        ]);
    }
}
