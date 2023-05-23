<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportUserController extends AbstractController
{
    #[Route('/admin/import/user', name: 'app_admin_import_user')]
    public function index(): Response
    {
        return $this->render('import_user/index.html.twig', [
            'controller_name' => 'ImportUserController',
        ]);
    }
}
