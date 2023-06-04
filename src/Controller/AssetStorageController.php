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
use App\Repository\SiteConfigRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/asset/storage')]
class AssetStorageController extends AbstractController
{
    #[Route('/', name: 'app_asset_storage_index', methods: ['GET'])]
    public function index(AssetStorageRepository $assetStorageRepository): Response
    {
        return $this->render('asset_storage/index.html.twig', [
            'asset_storages' => $assetStorageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_asset_storage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AssetStorageRepository $assetStorageRepository): Response
    {
        $assetStorage = new AssetStorage();
        $form = $this->createForm(AssetStorageType::class, $assetStorage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $assetStorageRepository->save($assetStorage, true);

            return $this->redirectToRoute('app_asset_storage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('asset_storage/new.html.twig', [
            'asset_storage' => $assetStorage,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_asset_storage_show')]
    public function show(Request $request, RepairPartsRepository $repairPartsRepository, ReportController $reportController, AssetStorage $assetStorage, UserRepository $userRepository, AssetCollectionRepository $assetCollectionRepository, AssetStorageRepository $assetStorageRepository, AssetRepository $assetRepository, SiteConfigRepository $siteConfigRepository, $id): Response
    {
        $assetUniqueIdentifier = $siteConfigRepository->findOneBy(['configName' => 'asset_unique_identifier'])->getConfigValue();
        $assetCollection = $assetCollectionRepository->findAll();
        $storage = $this->renderStorageView($assetStorageRepository->findOneBy(['id' => $id])->getStorageData());
        $storageCounts = $reportController->assetsPerStorage($assetStorageRepository, $assetCollectionRepository, $id);

        $collectedAssets = [];
        foreach ($assetCollection as $asset) {
            $user = $userRepository->findOneBy(['id' => $asset->getCollectedFrom()]);
            $collectedAssets[] = [
                'slot' => $asset->getCollectionLocation(),
                'asset' => ($assetUniqueIdentifier == 'assettag')
                    ? $assetRepository->findOneBy(['id' => $asset->getDeviceID()])->getAssettag()
                    : $assetRepository->findOneBy(['id' => $asset->getDeviceID()])->getSerialnumber(),
                'user' => $user->getId(),
                'usersName' => $user->getSurname() . ', ' . $user->getFirstname(),
                'note' => $asset->getCollectionNotes(),
                'checkedOut' => $asset->isCheckedout(),
                'processed' => $asset->isProcessed()
            ];
        }

        $form = $this->createForm(AssetCollectionType::class, $assetCollection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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

        $colors['cellOccupied'] = $siteConfigRepository->findOneBy(['configName' => 'collection_color_cell_occupied'])->getConfigValue();
        $colors['cellCheckedOut'] = $siteConfigRepository->findOneBy(['configName' => 'collection_color_cell_checkedout'])->getConfigValue();
        $colors['cellProcessed'] = $siteConfigRepository->findOneBy(['configName' => 'collection_color_cell_processed'])->getConfigValue();

        return $this->render('asset_storage/show.html.twig', [
            'assetStorage' => $assetStorage,
            'colors' => $colors,
            'repairParts' => $parts,
            'storageCounts' => $storageCounts,
            'storageRender' => $storage,
            'form' => $form,
            'collectedAssets' => $collectedAssets
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
    public function renderStorageView(?array $storageData): string
    {
        // TODO: Create this HTML in the twig file
//        if (null === $storageData) {
//            $this->render('asset_storage/storageRender.html.twig', [
//                'storage' => ''
//            ]);
//        }

        $html = '<div id="storageStart" class="">';
        foreach ($storageData as $side) {
            $html .= '<div id="storageContainerSide" class="col storageSides my-3 px-3">';

            foreach ($side as $row) {
                $html .= '<div id="storageContainerRow" class="row no-gutters storageRows">';

                foreach ($row as $id=>$slot) {
                    $html .= '
                    <div id="slot-'.$slot.'" class="col-sm p-0 storageCells">
                        <a href="#" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-html="true" data-bs-title="' . $slot . '">
                            <span data-bs-toggle="modal" data-bs-target="#modal-checkin" data-slot="'.$slot.'" id="slotNumber">
                                ' . $slot . '
                            </span>
                        </a>
                    </div><div class="col-sm"></div>';
                }

                $html .= '</div>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';
        return $html;
//        return $this->render('asset_storage/storageRender.html.twig', [
//            'storage' => $html
//        ]);
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
