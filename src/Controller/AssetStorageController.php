<?php

namespace App\Controller;

use App\Entity\AssetStorage;
use App\Form\AssetStorageType;
use App\Repository\AssetStorageRepository;
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

    #[Route('/{id}', name: 'app_asset_storage_show', methods: ['GET'])]
    public function show(AssetStorage $assetStorage): Response
    {
        return $this->render('asset_storage/show.html.twig', [
            'asset_storage' => $assetStorage,
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
     * @return string|null
     */
    public function renderStorageView(?array $storageData): ?string
    {
        if (null === $storageData) {
            return null;
        }

        $html = '<div id="storageStart" class="row">';
        foreach ($storageData as $side) {
            $html .= '<div id="side" class="col">';

            foreach ($side as $row) {
                $html .= '<div id="row" class="row align-items-center no-gutters">';

                foreach ($row as $id=>$slot) {
                    $html .= '<div id="slot" class="col"><a href="#" data-id="'.$id.'" data-toggle="modal" data-target="#slotModal">'.$slot.'</a></div>';
                }

                $html .= '</div>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

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
