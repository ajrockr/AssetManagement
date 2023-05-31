<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\ImportUserType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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

    /**
     * @throws \Exception
     */
    private function processUpload(EntityManagerInterface $entityManager, $file): void
    {
        if (($handle = fopen($file->getPathname(), "r")) !== false) {
            $userRepository = $entityManager->getRepository(User::class);
            try {
                while (($data = fgetcsv($handle)) !== false) {
                    // TODO: Eventually support files that have headers.
                    // Skip header, we know $data[0] is the Description
                    if (($data[0] == "Description")) {
                        continue;
                    }

                    // Bypass the unique constraint by skipping the record
                    if ($userRepository->findOneBy(['username' => $data[1]])) {
                        continue;
                    }

                    // We need an email field, skip if no email in record (bad import data)
                    if (empty($data[2])) {
                        continue;
                    }

                    $user = new User();
                    $user->setSurname($data[4]);
                    $user->setFirstname($data[3]);
                    $user->setUsername($data[1]);
                    $user->setEmail($data[2]);
                    $user->setTitle($data[0]);
//                    $user->setDepartment('Student');
                    $user->setRoles(['ROLE_DENY_LOGIN']);
                    $user->setEnabled(false);
                    $user->setUserUniqueId($data[5]);
                    $user->setType($data[6]);
                    $entityManager->persist($user);
                }

                $entityManager->flush();
            } catch (UniqueConstraintViolationException $e) {
                throw new \Exception($e->getMessage());
            }
        }
    }
}
