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
        $this->iiq->setRequestUrl('/assets/manufacturers');
        $this->iiq->setRequestMethod($this->iiq::HTTP_METHOD_GET);
        dd($this->iiq->sendRequest());
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
