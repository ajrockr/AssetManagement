<?php

namespace App\Controller;

use App\Entity\AssetCollection;
use App\Entity\AssetStorage;
use App\Form\AssetCollectionType;
use App\Form\AssetStorageType;
use App\Repository\AssetCollectionRepository;
use App\Repository\AssetRepository;
use App\Repository\AssetStorageRepository;
use App\Repository\RepairPartsRepository;
use App\Repository\RepairRepository;
use App\Repository\SiteConfigRepository;
use App\Repository\StorageLockRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/asset/storage')]
class AssetStorageController extends AbstractController
{
    private array $config;
    private array $storage;

    public function __construct(
        private readonly SiteConfigRepository $siteConfigRepository,
        private readonly AssetStorageRepository $assetStorageRepository
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
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_asset_storage_show')]
    public function showStorage(Request $request, StorageModerationController $storageModerationController, RepairRepository $repairRepository, RepairPartsRepository $repairPartsRepository, ReportController $reportController, AssetStorage $assetStorage, UserRepository $userRepository, AssetCollectionRepository $assetCollectionRepository, AssetRepository $assetRepository, $id): Response
    {
        $storageLocked = $storageModerationController->isLocked($id);
        $assetUniqueIdentifier = $this->config['asset_unique_identifier'];
        $collectedAssets = $assetCollectionRepository->getAll();
        $storageData = $this->assetStorageRepository->findOneBy(['id' => $id])->getStorageData();
//        $storage = $this->renderStorageView($storageData, $storageLocked);
        $storage = '';
        $storageCounts = $reportController->assetsPerStorage($this->assetStorageRepository, $assetCollectionRepository, $id);

        $users = $userRepository->getUsers();
        $repairs = $repairRepository->getAllOpen();

        $assets = [];
        foreach ($collectedAssets as $asset) {
            $user = $users[$asset['collected_from']];

            // Check if asset has a repair associated with it
            $repairAssets = array_column($repairs, 'asset_id');
            $hasRepair = in_array($asset['asset_id'], $repairAssets);

            $repairId = null;
            if ($hasRepair) {
                $repairId = $repairRepository->findOneBy(['assetId' => $asset['asset_id']])->getId();
            }

            $assets[] = [
                'slot' => $asset['location'],
                'asset' => ($assetUniqueIdentifier == 'assettag')
                    ? $asset['asset_tag']
                    : $asset['serial_number'],
                'user' => $user['id'],
                'usersName' => $user['surname'] . ', ' . $user['firstname'],
                'note' => $asset['notes'],
                'checkedOut' => $asset['checked_out'],
                'processed' => $asset['processed'],
                'hasRepair' => $hasRepair,
                'repairId' => $repairId
            ];
        }

        $form = $this->createForm(AssetCollectionType::class, $collectedAssets);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !$storageLocked) {
            if ($form->get('clearLocation')->isClicked()) {
                $clearLocation = $form->get('location')->getViewData();
                return $this->forward('App\Controller\StorageModerationController::clearLocation', ['location' => $clearLocation]);
            }

            return $this->forward('App\Controller\AssetController::checkIn', [
                'form' => $form
            ]);
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

        if ($storageLocked) {
            $this->addFlash('warning', 'This storage has been locked, editing has been disabled.');
        }

        return $this->render('asset_storage/show.html.twig', [
            'assetStorage' => $assetStorage,
            'colors' => $colors,
            'repairParts' => $parts,
            'storageCounts' => $storageCounts,
            'storageRender' => $storage,
            'storageData' => $storageData,
            'form' => $form,
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
            'form' => $form,
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
     * Renders the HTML that represents the storages
     *
     * @param array|null $storageData
     * @return Response
     */
//    public function renderStorageView(?array $storageData, bool $storageLocked = false): string
//    {
//        // TODO: Create this HTML in the twig file
////        if (null === $storageData) {
////            $this->render('asset_storage/storageRender.html.twig', [
////                'storage' => ''
////            ]);
////        }
//
//        $html = '<div id="storageStart">';
//
//        if (! preg_grep('/^side*/', array_keys($storageData))) {
//            // 'side*' does not exist, this is a 1 sided cart
//        }
//        foreach ($storageData as $side) {
//            if (preg_grep('/^row*/', array_keys($side))) {
//                // Do I care that 'row' wasn't part of the keys?
//            }
//            $html .= '<div id="storageContainerSide" class="col storageSides my-3 px-3">';
//
//            foreach ($side as $row) {
//                $html .= '<div id="storageContainerRow" class="row no-gutters storageRows">';
//
//                foreach ($row as $id=>$slot) {
//                    $html .= '
//                    <div id="slot-'.$slot.'" class="col-sm p-0 storageCell">
//                        <a href="javascript:void(0);" class="text-decoration-none my-asset-collection-btn" role="button" data-bs-toggle="tooltip" data-bs-html="true" data-bs-title="' . $slot . '" id="href-slot-' . $slot . '">
//                            <span data-bs-toggle="modal" data-bs-target="#modal-checkin" data-slot="'.$slot.'" id="slotNumber-'. $slot . '">' . $slot . '</span>
//                        </a>
//                    </div>
//                    <div class="col-sm"></div>';
//                }
//
//                $html .= '</div>';
//            }
//
//            $html .= '</div>';
//        }
//
//        $html .= '</div>';
//        return $html;
////        return $this->render('asset_storage/storageRender.html.twig', [
////            'storage' => $html
////        ]);
//    }

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
            // Pseudo for do not show
            if ($item->getLocation() == 0) {
                continue;
            }

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
