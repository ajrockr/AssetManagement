<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Form\AssetType;
use App\Entity\AssetCollection;
use App\Form\AssetCollectionType;
use App\Repository\UserRepository;
use App\Repository\AssetRepository;
use App\Repository\RepairRepository;
use App\Repository\SiteConfigRepository;
use App\Service\Logger;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AssetStorageRepository;
use App\Repository\AssetCollectionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/asset')]
#[IsGranted('ROLE_ASSET_READ')]
class AssetController extends AbstractController
{
    private int $lastSlotCollected;

    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly Logger $logger,
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
    #[IsGranted('ROLE_ASSET_MODIFY')]
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
    #[IsGranted('ROLE_ASSET_MODIFY')]
    public function new(Request $request, AssetRepository $assetRepository, SiteConfigRepository $siteConfigRepository): Response
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
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_asset_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ASSET_MODIFY')]
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
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_asset_delete')]
    #[IsGranted('ROLE_ASSET_MODIFY')]
    public function delete(Request $request, Asset $asset, AssetRepository $assetRepository): Response
    {
        try {
            $assetRepository->remove($asset, true);
        } catch (\Exception $e) {
            $this->addFlash('warning', 'Failed to delete asset.');
            return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
        }

        $this->addFlash('success', 'Deleted asset.');

        return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/checkin', name: 'app_asset_checkin')]
    #[IsGranted('ROLE_ASSET_MODIFY')]
    public function assetCheckIn(Request $request, SiteConfigRepository $siteConfigRepository, AssetRepository $assetRepository, AssetStorageRepository $assetStorageRepository, AssetCollectionRepository $assetCollectionRepository, UserRepository $userRepository): Response
    {
        // TODO: Fix form submit, forward to checkin, check unique identifier
        // Set up what is needed to render the page
        $users = $userRepository->findAll();
        $assetUniqueIdentifier = $siteConfigRepository->findOneBy(['configName' => 'asset_unique_identifier'])->getConfigValue();
        $form = $this->createForm(AssetCollectionType::class);
        $assets = $assetRepository->findAll();
        $returnAssetsArr = [];

        // No assets in the database, so skip this part
        if (null === $assets) {
            return $this->render('asset/checkin.html.twig', [
                'allAssets' => $returnAssetsArr,
                'form' => $form,
                'users' => $users
            ]);
        }

        foreach ($assetCollectionRepository->findAll() as $collectedAsset) {
            $collectedAssets[] = $collectedAsset->getDeviceID();
        }

        foreach ($assets as $asset) {
            if (!in_array($asset->getId(), $collectedAssets)) {
                $user = $userRepository->findOneBy(['id' => $asset->getAssignedTo()]);
                // Not collected
                $returnAssetsArr[] = [
                    'id' => $asset->getId(),
                    'assettag' => $asset->getAssettag(),
                    'serialnumber' => $asset->getSerialnumber(),
                    'assignedTo' => (null === $user) ? null : $user->getSurname() . ', ' . $user->getFirstname(),
                    'assignedToId' => $asset->getAssignedTo()
                ];
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set up for the database insertion
            $configForceAssignUser = $siteConfigRepository->findOneBy(['configName' => 'asset_assignUser_on_checkin']);
            $data = $form->getData();

            // TODO: Check to make sure the storage exists
            $loggedInUserId = $this->getUser()->getId();

            // TODO: Add the date field to the form
            $date = null;

            switch ($assetUniqueIdentifier) {
                case 'assettag':
                    $deviceId = $assetRepository->findOneBy(['assettag' => $data['device']])->getId();
                    break;
                case 'serialnumber':
                    $deviceId = $assetRepository->findOneBy(['serialnumber' => $data['device']])->getId();
                    break;
            }

            // Set the asset collection
            $assetCollection = new AssetCollection();
            $assetCollection->setCollectedDate($date ?? new \DateTimeImmutable('now'));
            $assetCollection->setCollectedBy($loggedInUserId);
            $assetCollection->setCollectionLocation($data['location']);
            $assetCollection->setDeviceID($deviceId);
            $assetCollection->setCollectedFrom($data['user']);
            $assetCollection->setCheckedout(false);
            $assetCollection->setCollectionNotes($data['notes']);

            $assetCollectionRepository->save($assetCollection, true);

            // If the asset is not assigned or the config value to overwrite the assigned user is true,
            // overwrite the assigned user.
            $asset = $assetRepository->findOneBy(['id' => $deviceId]);
            if (null === $asset->getAssignedTo() || $configForceAssignUser->getConfigValue()) {
                $asset->setAssignedTo($data['user']);
                $assetRepository->save($asset, true);
            }

            return $this->redirectToRoute('app_asset_checkin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('asset/checkin.html.twig', [
            'allAssets' => $returnAssetsArr,
            'form' => $form,
            'users' => $users
        ]);
    }

    public function checkIn(Request $request, SiteConfigRepository $siteConfigRepository, AssetRepository $assetRepository, UserRepository $userRepository, AssetCollectionRepository $assetCollectionRepository, RepairRepository $repairRepository, RepairController $repairController, $form, array $neededParts = [], bool $return = false): bool|Response
    {
        // Set up what is needed to render the page
        $users = $userRepository->findAll();
        $assetUniqueIdentifier = $siteConfigRepository->findOneBy(['configName' => 'asset_unique_identifier'])->getConfigValue();

        // Set up for the database insertion
        $configForceAssignUser = $siteConfigRepository->findOneBy(['configName' => 'asset_assignUser_on_checkin']);

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
            ];
        } else {
            $data = $form->getData();
        }

        $loggedInUserId = $this->getUser()->getId();

        // TODO Add the date field to the form
        $date = null;

        $device = null;
        switch ($assetUniqueIdentifier) {
            case 'assettag':
                $device = $assetRepository->findOneBy(['asset_tag' => $data['device']]);
                break;
            case 'serialnumber':
                $device = $assetRepository->findOneBy(['serial_number' => $data['device']]);
                break;
        }

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
//            $assetRepository->save($asset, true);
            $deviceId = $asset->getId();
        } else {
            $deviceId = $device->getId();
        }

        $action = false;
        // Device already collected, update the record
        if ($assetCollection = $assetCollectionRepository->findOneBy(['collectionLocation' => $data['location']])) {
            $assetCollection->setCollectedDate($date ?? new \DateTimeImmutable('now'))
                ->setCollectedBy($loggedInUserId)
                ->setCollectionLocation($data['location'])
                ->setDeviceID($deviceId)
                ->setCollectedFrom($data['user'])
                ->setCheckedout($data['checkout'])
                ->setProcessed($data['processed'])
                ->setCollectionNotes($data['notes'])
                ->setCollectionStorage($data['storageId'])
            ;
            $action = 'update';
        // Device not collected, create the collection record
        } else {
            $assetCollection = new AssetCollection();
            $assetCollection->setCollectedDate($date ?? new \DateTimeImmutable('now'))
                ->setCollectedBy($loggedInUserId)
                ->setCollectionLocation($data['location'])
                ->setDeviceID($deviceId)
                ->setCollectedFrom($data['user'])
                ->setCheckedout($data['checkout'])
                ->setProcessed($data['processed'])
                ->setCollectionNotes($data['notes'])
                ->setCollectionStorage($data['storageId'])
            ;
            $action = 'create';
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
        } catch(\Exception $e) {
            // Failed, get out of here with Flash Message
            $this->addFlash('error', 'Failed collecting asset ['.$data['device'].'].');
            return $this->redirect($request->headers->get('referer'));
        }

        // If the asset is not assigned or the config value to overwrite the assigned user is true,
        // overwrite the assigned user.
        $asset = $assetRepository->findOneBy(['id' => $deviceId]);
        if (null === $asset->getAssignedTo() || $configForceAssignUser->getConfigValue()) {
            $asset->setAssignedTo($data['user']);

            try {
                $assetRepository->save($asset, true);
            } catch(\Exception $e) {
                // TODO commenting out for testing
                // $this->addFlash('warning', 'Failed assign user to device ['.$data['device'].'].');
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

        // TODO again, do I just make this return bool. No flash messages, no nothing...?
        // if ($action == 'update') {
        //     $this->addFlash('success', 'Asset (' . $data['device'] . ') assigned to slot (' . $data['location'] . ')');
        // } elseif ($action == 'create') {
        //     $this->addFlash('success', 'Asset (' . $data['device'] . ') has been updated on slot (' . $data['location'] . ')');
        // } else {
        //     $this->addFlash('warning', 'Something went wrong, could not update asset (' . $data['device'] . ') in slot ' . $data['location'] . '.');
        // }

        // if ($return) {
        //     return true;
        // }

        // TODO I'm returning bool here for testing, this might break other parts of the site for now
//        return true;
         return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/collection/collect', name: 'app_asset_collection_collect')]
    #[IsGranted('ROLE_ASSET_MODIFY')]
    public function checkInForm(Request $request, SiteConfigRepository $siteConfigRepository, RepairRepository $repairRepository, AssetRepository $assetRepository, RepairController $repairController, AssetStorageRepository $assetStorageRepository, UserRepository $userRepository, AssetCollectionRepository $assetCollectionRepository, ?string $requestingPath = null, $requestingPathParams = []): Response|array
    {
        // TODO also, select2 styling is weird on different pages. make sure that is uniform
        // TODO can I deny someone from accessing this through the browser?

        // Form 1) Select which cart
        $storages = $assetStorageRepository->findAll();

        $storagesFormArray = [];
        foreach ($storages as $storage) {
            $storagesFormArray[$storage->getName() . ' (' . $storage->getDescription() . ')'] = $storage->getId();
        }

        // Form 2) Fetch all users
        $users = $userRepository->findAll();

        $usersFormArray = [];
        foreach ($users as $user) {
            if ($uid = $user->getUserUniqueId()) {
                $usersFormArray[$user->getSurname() . ', ' . $user->getFirstname() . ' (' . $user->getUserUniqueId() . ')'] = $user->getId();
            } else {
                $usersFormArray[$user->getSurname() . ', ' . $user->getFirstname()] = $user->getId();
            }
        }

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

        // Set up forms
        $storageFullForm = null;
        $collectionForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_asset_collection_collect'))
            ->add('storage', ChoiceType::class, [
                'choices' => $storagesFormArray
            ])
            ->add('user', ChoiceType::class, [
                'choices' => $usersFormArray
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

        $nextOpenSlot = null;

        if ($collectionForm->isSubmitted() && $collectionForm->isValid()) {
            $data = $collectionForm->getData();

            $requestingPath = $data['requestingPath'];
            $requestingPathParams[$data['requestingPathParamKey']] = $data['requestingPathParamVal'];

            // Get storage data
            $storageData = $assetStorageRepository->getStorageData($data['storage']);

            // Get collected assets
            $assignedAssets = $assetCollectionRepository->getCollectedAssetSlots();

            $openStorageSlots = array_map('intval', array_diff($storageData, $assignedAssets));

            // Check to see if asset is already collected
            $assetId = $assetRepository->findAssetId($data['asset_tag'], $data['asset_serial']);
            if ($assetCollected = $assetCollectionRepository->findOneBy(['DeviceID' => $assetId])) {
                $storageName = $assetStorageRepository->findOneBy(['id' => $assetCollected->getCollectionStorage()])->getName();
                $this->addFlash('assetAlreadyCollected', [$assetCollected->getCollectionLocation(), $storageName]);
            }

            // Check to see if Storage is full
            elseif (count($openStorageSlots) == 0) {
                // return no open slots, ask user to place asset aside?
                // TODO fix this
                dd('Storage is full, place asset aside?');

                $storageFullForm = $this->createFormBuilder()
                    ->add('storage_full_place_aside', CheckboxType::class)
                    ->add('Collect', SubmitType::class)
                    ->getForm()
                ;
            } else {
                // pseudo for now, allow this to be changed in config
                $config['storage_collection_sort_slots_order'] = 'asc';
                if ($config['storage_collection_sort_slots_order'] === 'desc') {
                    rsort($openStorageSlots);
                } else {
                    // If 'desc' is not the config value or 'asc' is defined, default to ascending
                    sort($openStorageSlots);
                }

                $nextOpenSlot = reset($openStorageSlots);

                $data['location'] = $nextOpenSlot;

                $this->checkIn($request, $siteConfigRepository, $assetRepository, $userRepository, $assetCollectionRepository, $repairRepository, $repairController, $data, [], true);
                // TODO set a flash message
                $this->addFlash('assetCollected', $nextOpenSlot);
            }

            return $this->redirectToRoute($requestingPath, [
                $data['requestingPathParamKey'] => $data['requestingPathParamVal'],
            ]);
        }

        return $this->render('asset_collection/collectionForm.html.twig', [
            'collectionForm' => $collectionForm->createView(),
            'storageFullForm' => (null === $storageFullForm) ? '' : $storageFullForm->createView(),
        ]);
    }

    public function setLastSlotCollected(int $slotNumber): void
    {
        $this->lastSlotCollected = $slotNumber;
    }

    public function getLastSlotCollected(): int
    {
        return $this->lastSlotCollected;
    }
}
