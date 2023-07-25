<?php

namespace App\Controller;

use App\Plugin\IIQ\Plugin;
use App\Repository\AssetCollectionRepository;
use App\Repository\AssetRepository;
use App\Repository\AssetStorageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly AssetRepository $assetRepository,
        private readonly AssetStorageRepository $assetStorageRepository,
        private readonly AssetCollectionRepository $assetCollectionRepository,
        private readonly Plugin $iiq
    )
    {
        // dd($this->iiq->getAssetById("ae9b69a2-2e23-442b-90dc-cb5fd0ddb43d"));
        // dd($this->iiq->getAssetByTag("MOBILE-1978LT2"));
        // dd($this->iiq->searchForAsset("1978LT2"));
        // dd($this->iiq->getParts());
        // dd($this->iiq->getSuppliers());
        // dd($this->iiq->getUsers());
        dd($this->iiq->test());
    }
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'totalAssetsCount' => $this->getTotalAssetsCount(),
            'totalCollectedAssetsCount' => $this->getCollectedAssetsCount(),
            'totalDecommissionedAssetsCount' => $this->getDecommissionedAssetsCount(),
            'totalStoragesCount' => $this->getStorageCount(),
            'storageSizes' => $this->getStorageSizes()
        ]);
    }

    private function getStorageCount(): int
    {
        return count($this->assetStorageRepository->findAll());
    }

    private function getStorageSizes(): array
    {
        $storages = $this->assetStorageRepository->findAll();
        $returnArray = [];
        foreach ($storages as $storage) {
            $returnArray[$storage->getName()] = sizeof($storage->getStorageData(), 1);
        }

        return $returnArray;
    }

    private function getTotalAssetsCount(): int
    {
        return count($this->assetRepository->findAll());
    }

    private function getCollectedAssetsCount(): int
    {
        return count($this->assetCollectionRepository->findAll());
    }

    private function getDecommissionedAssetsCount(): int
    {
        return count($this->assetRepository->findBy(['decomisioned' => true]));
    }
}
