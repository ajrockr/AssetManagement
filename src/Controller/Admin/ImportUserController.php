<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\ImportUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ImportUserController extends AbstractController
{
    #[Route('/admin/import/user', name: 'app_admin_import_user')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ImportUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->get('fileCsv')->getData();
            
            $this->processUpload($entityManager, $data);
        }

        return $this->render('admin/import_user/index.html.twig', [
            'importUserForm' => $form
        ]);
    }

    private function processUpload(EntityManagerInterface $entityManager, $file): void
    {
        if (($handle = fopen($file->getPathname(), "r")) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                $user = new User();
                $user->setSurname($data[4]);
                $user->setFirstname($data[3]);
                $user->setUsername($data[1]);
                $user->setEmail($data[1] . '@westex.org');
                $user->setTitle($data[0]);
                $user->setDepartment('Student');
                $user->setRoles(['ROLE_DENY_LOGIN']);
                $user->setEnabled(false);
                $entityManager->persist($user);
            }

            $entityManager->flush();
        }
    }
}
