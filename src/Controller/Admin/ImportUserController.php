<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\ImportUserType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImportUserController extends AbstractController
{
    /**
     * @var array
     */
    private array $errors = [];

    /**
     * @var array|string[]
     */
    private array $acceptedKeys = [
        'Username',
        'Email',
        'Title',
        'Department',
        'UniqueID',
        'FirstName',
        'Surname',
        'Roles',
        'Enabled'
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('/admin/import/user', name: 'app_admin_import_user')]
    public function index(Request $request, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ImportUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('fileCsv')->getData();
            $hasHeader = $form->get('hasHeader')->getData();

            if ($file) {
                // Set the filename
                $datetime = new \DateTimeImmutable();
                $format = $datetime->format('Ymdhi');
                $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalFileName);
                $newFileName = $safeFileName . uniqid() . '-' . $format . '.' . $file->guessExtension();

                // Move the uploaded file
                $uploadsDir = $this->getParameter('user_import_dir');
                try {
                    $file->move(
                        $uploadsDir,
                        $newFileName
                    );
                } catch (FileException $e) {
                    throw new $e->getMessage();
                }

                // Map an array from the CSV file
                $csv = array_map('str_getcsv', file($uploadsDir . DIRECTORY_SEPARATOR . $newFileName, FILE_SKIP_EMPTY_LINES));

                // If the CSV is stated to have a header
                if ($hasHeader) {
                    $csvArray = $this->convertToAssociativeArray($csv);
                }

                // If errors were passed
                if (count($this->errors) > 0) {
                    // TODO: Delete the file ($file->delete();)
                    return $this->render('admin/import_user/index.html.twig', [
                        'importUserForm' => $form,
                        'errors' => $this->errors
                    ]);
                }

                if (isset($array)) {
                    // Process csv with header
                } else {
                    $this->processCsvWithoutHeader($file);
                }
            }
        }

        return $this->render('admin/import_user/index.html.twig', [
            'importUserForm' => $form,
            'errors' => $this->errors
        ]);
    }

    /**
     * Create an associative array
     *
     * @param array $array
     * @return array|null
     */
    private function convertToAssociativeArray(array $array): ?array
    {
        $keys = array_map('strtolower', array_shift($array));
        $acceptedKeys = array_map('strtolower', $this->acceptedKeys);
        foreach ($keys as $key) {
            if (!in_array($key, $acceptedKeys)) {
                $this->errors[] = [
                    'message' => 'Invalid header(s). Valid header keys include:',
                    'value' => $this->acceptedKeys
                ];

                return null;
            }
        }

        foreach ($array as $i => $row) {
            $array[$i] = array_combine($keys, $row);
        }

        return $array;
    }

    /**
     * Import users when a header is provided
     *
     * @param array $csv
     * @return void
     */
    private function processCsvWithHeader(array $csv): void
    {
        try {
            foreach ($csv as $item) {
                $roles = (array_key_exists('roles')) ? [$item['roles']] : [];
                $enabled = (array_key_exists('enabled')) ? $item['enabled'] : false;

                $user = new User();
                $user->setSurname($item['surname']);
                $user->setFirstname($item['first name']);
                $user->setUsername($item['username']);
                $user->setEmail($item['email']);
                $user->setTitle($item['title']);
                $user->setRoles($roles);
                $user->setEnable($enabled);
                $user->setUserUniqueId($item['uniqueid']);
                $user->setType($item['type']);
                $this->entityManager->persist($user);
            }
        } catch (Exception $e) {
            $this->errors[] = [
                'message' => 'Failed importing users.',
                'value' => $e->getMessage()
            ];
        }
    }

    /**
     * Import users when a header is not provided
     *
     * @param $file
     * @return void
     */
    private function processCsvWithoutHeader($file): void
    {
        if (($handle = fopen($file->getPathname(), "r")) !== false) {
            $userRepository = $this->entityManager->getRepository(User::class);
            try {
                while (($data = fgetcsv($handle)) !== false) {
                    // Bypass the unique constraint by skipping the record
                    if ($userRepository->findOneBy(['username' => $data[1]])) {
                        $this->errors[] = [
                            'message' => 'Record skipped due to constrained username.',
                            'value' => $data[1]
                        ];
                        continue;
                    }

                    // We need an email field, skip if no email in record (bad import data)
                    if (empty($data[2])) {
                        $this->errors[] = [
                            'message' => 'Record skipped due to no email provided for user.',
                            'value' => $data[1]
                        ];
                        continue;
                    }

                    $user = new User();
                    $user->setSurname($data[4]);
                    $user->setFirstname($data[3]);
                    $user->setUsername($data[1]);
                    $user->setEmail($data[2]);
                    $user->setTitle($data[0]);
                    $user->setRoles(['ROLE_DENY_LOGIN']);
                    $user->setEnabled(false);
                    $user->setUserUniqueId($data[5]);
                    $user->setType($data[6]);
                    $this->entityManager->persist($user);
                }

                $this->entityManager->flush();
            } catch (Exception $e) {
                $this->errors[] = [
                    'message' => 'Failed importing users.',
                    'value' => $e->getMessage()
                ];
            }
        }
    }
}
