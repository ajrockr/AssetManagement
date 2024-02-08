<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Form\AssetType;
use App\Entity\AssetCollection;
use App\Repository\UserRepository;
use App\Repository\AssetRepository;
use App\Repository\RepairRepository;
use App\Repository\SiteConfigRepository;
use App\Service\Logger;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AssetStorageRepository;
use App\Repository\AssetCollectionRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/asset')]
//#[IsGranted('ROLE_ASSET_READ')]
class AssetController extends AbstractController
{
    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly Logger $logger,
        protected readonly AssetCollectionRepository $assetCollectionRepository,
        protected readonly AssetStorageRepository $assetStorageRepository,
    ) {}

    #[Route('/', name: 'app_asset_index', methods: ['GET'])]
    public function index(AssetRepository $assetRepository, UserRepository $userRepository): Response
    {
        $assets = $assetRepository->findAll();
        $users = $userRepository->findAll();
        $assetsArray = [];
        foreach ($assets as $asset) {
            $usersName = '';
            if ($asset->getAssignedTo()) {
                // Get user information
                if ($user = $userRepository->findOneBy(['id' => $asset->getAssignedTo()])) {
                    $usersName = $user->getFirstname() . ' ' . $user->getSurname();
                }
            }

            $assetsArray[] = [
                'id' => $asset->getId(),
                'serialnumber' => $asset->getSerialNumber(),
                'assettag' => $asset->getAssetTag(),
                'purchasedate' => $asset->getPurchaseDate(),
                'purchasedfrom' => $asset->getPurchasedFrom(),
                'warrantystartdate' => $asset->getWarrantyStartDate(),
                'warrantyenddate' => $asset->getWarrantyEndDate(),
                'condition' => $asset->getAssetCondition(),
                'make' => $asset->getMake(),
                'model' => $asset->getModel(),
                'assignedTo' => $usersName,
                'decommissioned' => $asset->isDecommissioned()
            ];
        }


        return $this->render('asset/index.html.twig', [
            'assets' => $assetsArray,
            'users' => $users
        ]);
    }

    #[Route('/assign', name: 'app_assign_user_to_device', methods: ['POST'])]
//    #[IsGranted('ROLE_ASSET_MODIFY')]
    public function assignUserToDevice(Request $request, AssetRepository $assetRepository, EntityManagerInterface $entityManager): Response
    {
        $data = $request->request->all();
        if ($asset = $assetRepository->findOneBy(['id' => $data['assetId']])) {
            $asset->setAssignedTo($data['userId']);
            $entityManager->persist($asset);
            $entityManager->flush();
            $this->addFlash('success', 'Assigned user to device.');
        } else {
            $this->addFlash('warning', 'Could not find asset.');
        }

        return $this->redirectToRoute('app_asset_index');
    }

    #[Route('/new', name: 'app_asset_new', methods: ['GET', 'POST'])]
//    #[IsGranted('ROLE_ASSET_MODIFY')]
    public function new(Request $request, AssetRepository $assetRepository): Response
    {
        $asset = new Asset();
        $form = $this->createForm(AssetType::class, $asset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $assetRepository->save($asset, true);

            $this->addFlash('success', 'Added new asset.');

            return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('asset/new.html.twig', [
            'asset' => $asset,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_asset_edit', methods: ['GET', 'POST'])]
//    #[IsGranted('ROLE_ASSET_MODIFY')]
    public function edit(Request $request, Asset $asset, AssetRepository $assetRepository): Response
    {
        $form = $this->createForm(AssetType::class, $asset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $assetRepository->save($asset, true);

            $this->addFlash('success', 'Edited asset.');

            return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('asset/edit.html.twig', [
            'asset' => $asset,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_asset_delete')]
//    #[IsGranted('ROLE_ASSET_MODIFY')]
    public function delete(Asset $asset, AssetRepository $assetRepository): Response
    {
        try {
            $assetRepository->remove($asset, true);
        } catch (Exception) {
            $this->addFlash('warning', 'Failed to delete asset.');
            return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
        }

        $this->addFlash('success', 'Deleted asset.');

        return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function checkIn(Request $request, SiteConfigRepository $siteConfigRepository, AssetRepository $assetRepository, AssetCollectionRepository $assetCollectionRepository, RepairRepository $repairRepository, RepairController $repairController, array|Form $form, array $neededParts = []): bool|Response
    {
        $assetUniqueIdentifier = $siteConfigRepository->findOneByName('asset_unique_identifier')->getConfigValue();

        // Set up for the database insertion
        $configForceAssignUser = $siteConfigRepository->findOneByName('asset_assignUser_on_checkin')->getConfigValue();

        if (is_array($form)) {
            $data = [
                'device' => $form['asset_tag'],
                'serial' => $form['asset_serial'],
                'user' => $form['user'],
                'location' => $form['location'],
                'storageId' => $form['storage'],
                'checkout' => false,
                'processed' => false,
                'notes' => '',
                'needsrepair' => false,
                'storageIsFull' => $form['storageIsFull']
            ];
        } else {
            $data = $form->getData();
            $data['storageIsFull'] = false;
        }

        $loggedInUserId = $this->getUser()->getId();

        // TODO Add the date field to the form
        $date = null;

        $device = null;
        // TODO Replace this with repository method that finds device by either asset_tag or serial_number. This will also need the AssetFormType to pull from config to see if asset/serial is required
        switch ($assetUniqueIdentifier) {
            case 'assettag':
                $device = $assetRepository->findOneBy(['asset_tag' => $data['device']]);
                break;
            case 'serialnumber':
                $device = $assetRepository->findOneBy(['serial_number' => $data['device']]);
                break;
        }

        // TODO make this a repository method that returns bool for checking
        // If device doesn't exist, create it
        if (null === $device) {
            $asset = new Asset;
            if ($assetUniqueIdentifier == 'assettag') {
                $asset->setAssetTag($data['device'])
                    ->setSerialNumber(null);
            } elseif ($assetUniqueIdentifier == 'serialnumber') {
                $asset->setSerialNumber($data['serial'])
                    ->setAssetTag(null);
            }
            $asset->setAssetCondition(null)
                ->setMake(null)
                ->setModel(null)
                ->setAssignedTo(null)
                ->SetDecommissioned(null)
                ->setPurchaseDate(null)
                ->setPurchasedFrom(null)
                ->setWarrantyEndDate(null)
                ->setWarrantyStartDate(null)
            ;
            $this->entityManager->persist($asset);
            $this->entityManager->flush();

            $deviceId = $asset->getId();
        } else {
            $deviceId = $device->getId();
        }

        // Check to see if asset is already collected
        // TODO Serial is not part of form in Storage view. This will have to be added and config will handle which fields are required
        $data['serial'] = null;
        $assetId = $assetRepository->findByAssetId($data['device'], $data['serial']);
        if ($assetCollected = $this->assetCollectionRepository->findOneBy(['DeviceID' => $assetId])) {
            $storageName = $this->assetStorageRepository->findOneBy(['id' => $assetCollected->getCollectionStorage()])->getName();
            $this->addFlash('assetAlreadyCollected', [$assetCollected->getCollectionLocation(), $storageName, false]);
            return $this->redirect($request->headers->get('referer'));
        }

        // Device already collected, update the record
        // TODO Make a conditional to check if asset already collected
        if ($assetCollection = $assetCollectionRepository->findOneBy(['collectionLocation' => $data['location']])) {
            $assetCollection->setCollectedDate($date ?? new \DateTimeImmutable('now'))
                ->setCollectedBy($loggedInUserId)
                ->setCollectionLocation($data['storageIsFull'] ? null : $data['location'])
                ->setDeviceID($deviceId)
                ->setCollectedFrom($data['user'])
                ->setCheckedout($data['checkout'])
                ->setProcessed($data['processed'])
                ->setCollectionNotes($data['notes'])
                ->setCollectionStorage($data['storageId'])
            ;

        // Device not collected, create the collection record
        } else {
            $assetCollection = new AssetCollection();
            $assetCollection->setCollectedDate($date ?? new \DateTimeImmutable('now'))
                ->setCollectedBy($loggedInUserId)
                ->setCollectionLocation($data['storageIsFull'] ? null : $data['location'])
                ->setDeviceID($deviceId)
                ->setCollectedFrom($data['user'])
                ->setCheckedout($data['checkout'])
                ->setProcessed($data['processed'])
                ->setCollectionNotes($data['notes'])
                ->setCollectionStorage($data['storageId'])
            ;
        }

        // Persist the collection record
        try {
            $assetCollectionRepository->save($assetCollection, true);
            $this->logger->assetCheckInOut($loggedInUserId,
                $deviceId,
                $this->logger::ACTION_ASSET_CHECKIN,
                $request->headers->get('referer'),
                $data['user'],
                $data['storage'] . $data['location']
            );
        } catch(Exception) {
            return $this->redirect($request->headers->get('referer'));
        }

        // If the asset is not assigned or the config value to overwrite the assigned user is true,
        // overwrite the assigned user.
        $asset = $assetRepository->findOneBy(['id' => $deviceId]);
        if (null === $asset->getAssignedTo() || $configForceAssignUser) {
            // TODO Make this a repository function
            $asset->setAssignedTo($data['user']);

            try {
                $assetRepository->save($asset, true);
            } catch(Exception) {
                // TODO this check doesn't belong here
//                 $this->addFlash('warning', 'Failed assign user to device ['.$data['device'].'].');
            }
        }

        // Check if Repair is needed
        if ($data['needsrepair']) {
            $repairData = [
                'asset' => $data['device'],
                'issue' => $data['notes'] ?? 'Issue not listed',
                'assetId' => $deviceId,
                'partsNeeded' => $neededParts
            ];
            $repairController->createRepair($assetRepository, $repairRepository, $repairData);
        }

         return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/collection/collect', name: 'app_asset_collection_collect', methods: 'POST')]
//    #[IsGranted('ROLE_ASSET_MODIFY')]
    public function checkInForm(Request $request, SiteConfigRepository $siteConfigRepository, RepairRepository $repairRepository, AssetRepository $assetRepository, RepairController $repairController, AssetStorageRepository $assetStorageRepository, UserRepository $userRepository, AssetCollectionRepository $assetCollectionRepository, ?string $requestingPath = null, $requestingPathParams = []): Response|array
    {
        // Form 1) Select which cart
        $storages = $assetStorageRepository->findAll();

        $storagesFormArray = [];
        foreach ($storages as $storage) {
            $storagesFormArray[$storage->getName() . ' (' . $storage->getDescription() . ')'] = $storage->getId();
        }

        // Form 2) Fetch all users
        $users = $userRepository->findAll();

        $usersFormArray = [];
//        dd($users);
        foreach ($users as $user) {
            if ('Admin' == $user->getUserIdentifier()) continue;
            if ($uid = $user->getUserUniqueId()) {
                $usersFormArray[$user->getSurname() . ', ' . $user->getFirstname() . ' (' . $uid . ')'] = $user->getId();
            } else {
                $usersFormArray[$user->getSurname() . ', ' . $user->getFirstname()] = $user->getId();
            }

        }
        array_unshift($usersFormArray, '');

        // TODO what happens when there is more than one?
        // TODO also...wtf is this? I forgot
        $pathParamKey = null;
        $pathParamVal = null;
        if (is_countable($requestingPathParams) && count($requestingPathParams) > 0) {
            foreach ($requestingPathParams as $key=>$val) {
                $pathParamKey = $key;
                $pathParamVal = $val;
            }
        }

        $collectionForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_asset_collection_collect'))
            ->add('storage', ChoiceType::class, [
                'choices' => $storagesFormArray
            ])
            ->add('user', ChoiceType::class, [
                'choices' => $usersFormArray,
                'multiple' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-control js-example-basic-single',
                ]
            ])
            ->add('asset_tag', TextType::class)
            ->add('asset_serial', TextType::class, [
                'required' => false
            ])
            ->add('requestingPath', HiddenType::class, [
                'attr' => [
                    'value' => $requestingPath
                ]
            ])
            ->add('requestingPathParamKey', HiddenType::class, [
                'attr' => [
                    'value' => $pathParamKey
                ]
            ])
            ->add('requestingPathParamVal', HiddenType::class, [
                'attr' => [
                    'value' => $pathParamVal
                ]
            ])
            ->getForm()
        ;

        $collectionForm->handleRequest($request);

        if ($collectionForm->isSubmitted() && $collectionForm->isValid()) {
            $data = $collectionForm->getData();

            $requestingPath = $data['requestingPath'];
            $requestingPathParams[$data['requestingPathParamKey']] = $data['requestingPathParamVal'];

            $storageData = $assetStorageRepository->getStorageData($data['storage']);

            $assignedAssets = $assetCollectionRepository->getCollectedAssetSlots();

            $openStorageSlots = array_map('intval', array_diff($storageData, $assignedAssets));

            $assetId = $assetRepository->findByAssetId($data['asset_tag'], $data['asset_serial']);

            $data['storageIsFull'] = false;

            // Check to see if asset is already collected
            if ($assetCollected = $assetCollectionRepository->findOneBy(['DeviceID' => $assetId])) {
                $storageName = $assetStorageRepository->findOneBy(['id' => $assetCollected->getCollectionStorage()])->getName();
                $this->addFlash('assetAlreadyCollected', [$assetCollected->getCollectionLocation(), $storageName, true]);
            } else {
                // Check to see if Storage is full
                if (count($openStorageSlots) == 0) {
                    $data['storageIsFull'] = true;
                    $this->addFlash('assetStorageIsFull', 'Storage is full');
                }

                $order = $siteConfigRepository->findOneByName('storage_collection_sort_slots_order')->getConfigValue();
                if ($order === 'desc') {
                    rsort($openStorageSlots);
                } else {
                    // If 'desc' is not the config value or 'asc' is defined, default to ascending
                    sort($openStorageSlots);
                }

                $nextOpenSlot = reset($openStorageSlots);

                $data['location'] = $nextOpenSlot;

                $this->checkIn($request, $siteConfigRepository, $assetRepository, $assetCollectionRepository, $repairRepository, $repairController, $data);
                $this->addFlash('assetCollected', $nextOpenSlot);
            }

            return $this->redirectToRoute($requestingPath, [
                $data['requestingPathParamKey'] => $data['requestingPathParamVal'],
            ]);
        }

        return $this->render('asset_collection/collectionForm.html.twig', [
            'collectionForm' => $collectionForm->createView(),
        ]);
    }
}
