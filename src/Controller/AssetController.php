<?php

namespace App\Controller;

use App\Entity\Asset;
use App\Entity\AssetCollection;
use App\Form\AssetCollectionType;
use App\Form\AssetType;
use App\Repository\AssetCollectionRepository;
use App\Repository\AssetRepository;
use App\Repository\AssetStorageRepository;
use App\Repository\SiteConfigRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Security("is_granted('ROLE_ASSET_READ') or is_granted('ROLE_ASSET_FULL_CONTROL') or is_granted('ROLE_ASSET_MODIFY') or is_granted('ROLE_SUPER_ADMIN')")]
#[Route('/asset')]
class AssetController extends AbstractController
{
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
                'serialnumber' => $asset->getSerialnumber(),
                'assettag' => $asset->getAssettag(),
                'purchasedate' => $asset->getPurchasedate(),
                'purchasedfrom' => $asset->getPurchasedfrom(),
                'warrantystartdate' => $asset->getWarrantystartdate(),
                'warrantyenddate' => $asset->getWarrantyenddate(),
                'condition' => $asset->getCondition(),
                'make' => $asset->getMake(),
                'model' => $asset->getModel(),
                'assignedTo' => $usersName,
                'decomisioned' => $asset->isDecomisioned()
            ];
        }


        return $this->render('asset/index.html.twig', [
            'assets' => $assetsArray,
            'users' => $users
        ]);
    }

    #[Security("is_granted('ROLE_ASSET_MODIFY') or is_granted('ROLE_ASSET_FULL_CONTROL') or is_granted('ROLE_SUPER_ADMIN')")]
    #[Route('/assign', name: 'app_assign_user_to_device', methods: ['POST'])]
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

    #[Security("is_granted('ROLE_ASSET_MODIFY') or is_granted('ROLE_ASSET_FULL_CONTROL') or is_granted('ROLE_SUPER_ADMIN')")]
    #[Route('/new', name: 'app_asset_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AssetRepository $assetRepository, SiteConfigRepository $siteConfigRepository): Response
    {
        $assetUniqueIdentifier = $siteConfigRepository->findOneBy(['configName' => 'asset_unique_identifier'])->getConfigValue();
        $asset = new Asset();
        $form = $this->createForm(AssetType::class, $asset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $assetRepository->save($asset, true);

            $this->addFlash('success', 'Added new asset.');

            return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('asset/new.html.twig', [
            'asset' => $asset,
            'form' => $form,
        ]);
    }

//    #[Security("is_granted('ROLE_ASSET_READ') or is_granted('ROLE_ASSET_FULL_CONTROL') or is_granted('ROLE_SUPER_ADMIN')")]
//    #[Route('/{id}', name: 'app_asset_show', methods: ['GET'])]
//    public function show(Asset $asset): Response
//    {
//        return $this->render('asset/show.html.twig', [
//            'asset' => $asset,
//        ]);
//    }

    #[Security("is_granted('ROLE_ASSET_EDIT') or is_granted('ROLE_ASSET_FULL_CONTROL') or is_granted('ROLE_SUPER_ADMIN')")]
    #[Route('/{id}/edit', name: 'app_asset_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Asset $asset, AssetRepository $assetRepository): Response
    {
        $form = $this->createForm(AssetType::class, $asset);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $assetRepository->save($asset, true);

            $this->addFlash('success', 'Edited asset.');

            return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('asset/edit.html.twig', [
            'asset' => $asset,
            'form' => $form,
        ]);
    }

    #[Security("is_granted('ROLE_ASSET_MODIFY') or is_granted('ROLE_ASSET_FULL_CONTROL') or is_granted('ROLE_SUPER_ADMIN')")]
    #[Route('/{id}/delete', name: 'app_asset_delete')]
    public function delete(Request $request, Asset $asset, AssetRepository $assetRepository): Response
    {
//        if ($this->isCsrfTokenValid('delete'.$asset->getId(), $request->request->get('_token'))) {
        try {
            $assetRepository->remove($asset, true);
        } catch (\Exception $e) {
            $this->addFlash('warning', 'Failed to delete asset.');
            return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
        }

        $this->addFlash('success', 'Deleted asset.');

        return $this->redirectToRoute('app_asset_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Security("is_granted('ROLE_ASSET_MODIFY') or is_granted('ROLE_ASSET_FULL_CONTROL') or is_granted('ROLE_ASSET_CHECKIN') or is_granted('ROLE_SUPER_ADMIN')")]
    #[Route('/checkin', name: 'app_asset_checkin')]
    public function assetCheckIn(Request $request, SiteConfigRepository $siteConfigRepository, AssetRepository $assetRepository, AssetStorageRepository $assetStorageRepository, AssetCollectionRepository $assetCollectionRepository, UserRepository $userRepository): Response
    {
//        dd($assetCollectionRepository->findOneBy(['collectionLocation' => '1002']) && $assetStorageRepository->storageDataExists('1003'));
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

        foreach ($assets as $asset) {
            if (null != $existingCheckin = $assetCollectionRepository->findOneBy(['DeviceID' => $asset->getId()])) {
                if (false === $existingCheckin->isCheckedout()) {
                        continue;
                    }
            }

            $assignedUser = $userRepository->findOneBy(['id' => $asset->getAssignedTo()]);

            $returnAssetsArr[] = [
                'id' => $asset->getId(),
                'assignedUserId' => $asset->getAssignedTo(),
                'uniqueIdentifier' => ('assettag' == $assetUniqueIdentifier) ? $asset->getAssettag() : $asset->getSerialnumber(),
                'assignedUsersName' => (null === $assignedUser) ? null : $assignedUser->getSurname() . ', ' . $assignedUser->getFirstname() . ' (' . $assignedUser->getTitle() . ')',
                'assignedUserId' => (null === $assignedUser) ?: $assignedUser->getId()
            ];
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

    public function checkIn(Request $request, SiteConfigRepository $siteConfigRepository, AssetRepository $assetRepository, UserRepository $userRepository, AssetCollectionRepository $assetCollectionRepository, $form): Response
    {
        // Set up what is needed to render the page
        $users = $userRepository->findAll();
        $assetUniqueIdentifier = $siteConfigRepository->findOneBy(['configName' => 'asset_unique_identifier'])->getConfigValue();

        // Set up for the database insertion
        $configForceAssignUser = $siteConfigRepository->findOneBy(['configName' => 'asset_assignUser_on_checkin']);
        $data = $form->getData();

        $loggedInUserId = $this->getUser()->getId();

        // TODO: Add the date field to the form
        $date = null;

        switch ($assetUniqueIdentifier) {
            case 'assettag':
                $device = $assetRepository->findOneBy(['assettag' => $data['device']]);
                break;
            case 'serialnumber':
                $device = $assetRepository->findOneBy(['serialnumber' => $data['device']]);
                break;
        }
        // TODO: First check if exists, update if so, else insert

        // Set the asset collection
        $assetCollection = new AssetCollection();

        // If device doesn't exist, create it
        if (null === $device) {
            $asset = new Asset;
            if ($assetUniqueIdentifier == 'assettag') {
                $asset->setAssettag($data['device']);
            } elseif ($assetUniqueIdentifier == 'serialnumber') {
                $asset->setSerialnumber($data['device']);
            }

            $assetRepository->save($asset, true);
            $deviceId = $asset->getId();
        } else {
            $deviceId = $device->getId();
        }

        if ($check = $assetCollectionRepository->findOneBy(['collectionLocation' => $data['location']])) {
            $check->setCollectedDate($date ?? new \DateTimeImmutable('now'))
                ->setCollectedBy($loggedInUserId)
                ->setCollectionLocation($data['location'])
                ->setDeviceID($deviceId)
                ->setCollectedFrom($data['user'])
                ->setCheckedout(false)
                ->setCollectionNotes($data['notes']);

            $assetCollectionRepository->save($check, true);
        } else {
            $assetCollection->setCollectedDate($date ?? new \DateTimeImmutable('now'));
            $assetCollection->setCollectedBy($loggedInUserId);
            $assetCollection->setCollectionLocation($data['location']);
            $assetCollection->setDeviceID($deviceId);
            $assetCollection->setCollectedFrom($data['user']);
            $assetCollection->setCheckedout(false);
            $assetCollection->setCollectionNotes($data['notes']);

            $assetCollectionRepository->save($assetCollection, true);
        }

        // If the asset is not assigned or the config value to overwrite the assigned user is true,
        // overwrite the assigned user.
        $asset = $assetRepository->findOneBy(['id' => $deviceId]);
        if (null === $asset->getAssignedTo() || $configForceAssignUser->getConfigValue()) {
            $asset->setAssignedTo($data['user']);
            $assetRepository->save($asset, true);
        }

        return $this->redirect($request->headers->get('referer'));

//        return $this->render('asset/_checkin_form.html.twig', [
//            'form' => $form,
//            'users' => $users
//        ]);
    }

    // TODO: Idea is to take the data from AssetStorage::storageData and render it in a human readable view
    public function renderStorageView() {}
}
