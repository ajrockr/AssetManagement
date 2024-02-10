<?php

namespace App\Controller;

use App\Entity\AssetStorage;
use App\Form\AssetCollectionType;
use App\Form\AssetStorageType;
use App\Repository\AssetCollectionRepository;
use App\Repository\AssetRepository;
use App\Repository\AssetStorageRepository;
use App\Repository\RepairPartsRepository;
use App\Repository\RepairRepository;
use App\Repository\SiteConfigRepository;
use App\Repository\UserRepository;
use App\Service\AssetCollectionService;
use App\Service\StorageModerationService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/asset/storage')]
class AssetStorageController extends AbstractController
{
    private array $config;

    public function __construct(
        private readonly SiteConfigRepository $siteConfigRepository,
        private readonly AssetStorageRepository $assetStorageRepository,
        private readonly AssetCollectionService $assetCollectionService,
        private readonly StorageModerationService $storageModerationService,
        private readonly AssetCollectionRepository $assetCollectionRepository,
    )
    {
        $this->config = $this->siteConfigRepository->getAllConfigItems();
    }
    #[Route('/', name: 'app_asset_storage_index', methods: ['GET'])]
    public function index(AssetStorageRepository $assetStorageRepository): Response
    {
        return $this->render('asset_storage/index.html.twig', [
            'asset_storages' => $assetStorageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_asset_storage_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $assetStorage = new AssetStorage();
        $form = $this->createForm(AssetStorageType::class, $assetStorage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->assetStorageRepository->save($assetStorage, true);

            return $this->redirectToRoute('app_asset_storage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('asset_storage/new.html.twig', [
            'asset_storage' => $assetStorage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/{id}', name: 'app_asset_storage_show')]
    public function showStorage(Request $request, AssetController $assetController, StorageModerationController $storageModerationController, RepairRepository $repairRepository, RepairPartsRepository $repairPartsRepository, ReportController $reportController, AssetStorage $assetStorage, UserRepository $userRepository, AssetCollectionRepository $assetCollectionRepository, AssetRepository $assetRepository, $id): Response
    {
        $storageLocked = $storageModerationController->isLocked($id);
        $collectedAssets = $assetCollectionRepository->getAllCollectedAssetsByStorageId($id);
        $storage = $this->assetStorageRepository->findOneBy(['id' => $id]);
        $storageData = $storage->getStorageData();
        $storageCounts = $reportController->assetsPerStorage($this->assetStorageRepository, $assetCollectionRepository, $id);

        $users = $userRepository->getUsers();
        $repairs = $repairRepository->getAllOpen();

        $assets = [];
        foreach ($collectedAssets as $asset) {
            $user = $users[$asset['assetcollection_CollectedFrom']];

            // Check if asset has a repair associated with it
            $repairAssets = array_column($repairs, 'asset_id');
            $hasRepair = in_array($asset['assetcollection_id'], $repairAssets);

            $repairId = null;
            if ($hasRepair) {
                $repairId = $repairRepository->findOneBy(['assetId' => $asset['asset_id']])->getId();
            }

            $assets[] = [
                'slot' => $asset['assetcollection_collectionLocation'],
                'asset_tag' => $asset['asset_tag'],
                'user' => $user['id'],
                'usersName' => $user['surname'] . ', ' . $user['firstname'],
                'note' => $asset['assetcollection_collectionNotes'],
                'checkedOut' => $asset['assetcollection_checkedout'],
                'processed' => $asset['assetcollection_processed'],
                'hasRepair' => $hasRepair,
                'repairId' => $repairId,
                'serial_number' => $asset['serial_number'],
            ];
        }

        $form = $this->createForm(AssetCollectionType::class, $collectedAssets);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$storageLocked) {
            $data = $form->getData();

            if ($form->get('clearLocation')->isClicked()) {
                $clearStorage = $this->assetCollectionRepository->findOneBy(['collectionLocation' => $data['location'], 'collectionStorage' => $data['storageId']]);

                $this->storageModerationService->clearLocation($clearStorage->getId(), $this->getUser()->getId());

                $this->addFlash('info', 'Cleared location(s) of collected assets.');
                return $this->redirect($request->headers->get('referer'));
            }

            // Check the asset into collection
            $asset = $this->assetCollectionService->createOrUpdateAsset($form->getData());

            if ($collected = $this->assetCollectionService->assetIsCollected($asset->getId())) {
                if ($collected->getDeviceID() != $asset->getId()) {
                    $this->addFlash('assetAlreadyCollected', [$collected->getCollectionLocation(), $storage->getName(), false]);
                    return $this->redirect($request->headers->get('referer'));
                }
            }

            $this->assetCollectionService->checkIn($form->getData(), $this->getUser()->getId());

            return $this->redirect($request->headers->get('referer'));
        }

        $repairParts = $repairPartsRepository->findAll();
        $parts = [];
        foreach ($repairParts as $repairPart) {
            if (null === $repairPart) {
                continue;
            }
            $parts[] = [
                'name' => $repairPart->getName(),
                'value' => $repairPart->getName()
            ];
        }

        $colors['cellOccupied'] = $this->config['collection_color_cell_occupied'];
        $colors['cellCheckedOut'] = $this->config['collection_color_cell_checkedout'];
        $colors['cellProcessed'] = $this->config['collection_color_cell_processed'];
        $colors['cellHasRepair'] = $this->config['collection_color_cell_hasrepair'];

        return $this->render('asset_storage/show.html.twig', [
            'storageId' => $id,
            'assetStorage' => $assetStorage,
            'colors' => $colors,
            'repairParts' => $parts,
            'storageCounts' => $storageCounts,
            'storageData' => $storageData,
            'form' => $form->createView(),
            'collectedAssets' => $assets,
            'storageLocked' => $storageLocked ? 'true' : 'false'
        ]);
    }

    #[Route('/{id}/edit', name: 'app_asset_storage_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AssetStorage $assetStorage, AssetStorageRepository $assetStorageRepository): Response
    {
        $form = $this->createForm(AssetStorageType::class, $assetStorage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $assetStorageRepository->save($assetStorage, true);

            return $this->redirectToRoute('app_asset_storage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('asset_storage/edit.html.twig', [
            'asset_storage' => $assetStorage,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_asset_storage_delete', methods: ['POST'])]
    public function delete(Request $request, AssetStorage $assetStorage, AssetStorageRepository $assetStorageRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$assetStorage->getId(), $request->request->get('_token'))) {
            $assetStorageRepository->remove($assetStorage, true);
        }

        return $this->redirectToRoute('app_asset_storage_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Renders storage items for use in the navigation
     *
     * @param AssetStorageRepository $assetStorageRepository
     * @return Response
     */
    public function renderNav(AssetStorageRepository $assetStorageRepository): Response
    {
        $storage = $assetStorageRepository->findAll();
        $array = [];
        foreach ($storage as $item) {
            $array[] = [
                'name' => $item->getName(),
                'id' => $item->getId(),
                'description' => $item->getDescription()
            ];
        }

        return $this->render('asset_storage/_nav.html.twig', [
            'items' => $array
        ]);
    }
}
