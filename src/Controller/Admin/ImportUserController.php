<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\ImportUserType;
use App\Service\Logger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER_ADMIN')]
class ImportUserController extends AbstractController
{
    public function __construct(private readonly Logger $logger) {}
    #[Route('/admin/import/user', name: 'app_admin_import_user')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ImportUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->get('fileCsv')->getData();

            $this->processUpload($entityManager, $data);
            // todo 1) Process upload 2) present user with data and column mappings 3) commit to database
        }

        return $this->render('admin/import_user/index.html.twig', [
            'importUserForm' => $form
        ]);
    }

    private function processUpload(EntityManagerInterface $entityManager, $file): void
    {
        $userData = [];
        if (($handle = fopen($file->getPathname(), 'r')) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                // TODO: Eventually support files that have headers.
                // Skip header, we know $data[0] is the Description
                if (($data[0] == 'Description')) {
                    continue;
                }

                $type = (empty($data[6])) ? 'Student' : $data[6];
                $userData[] = [
                    'title' => $data[0],
                    'surname' => $data[4],
                    'firstname' => $data[3],
                    'email' => $data[2],
                    'username' => $data[1],
                    'uniqueId' => $data[5],
                    'type' => $type,
                ];
            }
        }

        $this->commitToDatabase($entityManager, $userData);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param array $userData
     * @return void
     */
    private function commitToDatabase(EntityManagerInterface $entityManager, array $userData): void
    {
        $userRepository = $entityManager->getRepository(User::class);
        $logData = [];

        try {
            foreach ($userData as $user) {
                // Skip if username already exists
                if ($userRepository->findOneBy(['username' => $user['username']])) {
//                    $this->addFlash('warning', sprintf('User %s was skipped due to username conflicts.', $user['username']));
                    $user['status'] = 'skipped, user already exists';
                } else {
                    $userEntity = new User();
                    $userEntity->setSurname($user['surname']);
                    $userEntity->setFirstname($user['firstname']);
                    $userEntity->setUsername($user['username']);
                    $userEntity->setEmail($user['email']);
                    $userEntity->setTitle($user['title']);
                    $userEntity->setRoles(['ROLE_DENY_LOGIN']);
                    $userEntity->setEnabled(false);
                    $userEntity->setUserUniqueId($user['uniqueId']);
                    $userEntity->setType($user['type']);
                    $entityManager->persist($userEntity);
                }

                $logData[] = $user;
            }

            $entityManager->flush();
            $this->addFlash('success', 'Imported users.');
        } catch (ORMException $e) {
            $this->addFlash('error', 'Failed to import users.');
        }

        $this->logger->importUsers($this->getUser()->getId(), $logData, Logger::SOURCE_IMPORT_USER);
    }
}
