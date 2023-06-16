<?php

namespace App\Controller\Admin;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UtilityController extends AbstractController
{

    #[Route('/admin/utility', name: 'app_admin_utility')]
    public function index(Connection $connection): Response
    {
//        dd($connection->);
        return $this->render('admin/utility/index.html.twig', [
            'controller_name' => 'UtilityController',
        ]);
    }
}
