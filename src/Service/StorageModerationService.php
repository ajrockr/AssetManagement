<?php

namespace App\Service;

use App\Entity\AssetCollection;
use App\Repository\AssetCollectionRepository;
use App\Repository\AssetStorageRepository;
use Doctrine\ORM\EntityManagerInterface;

class StorageModerationService
{
    public function __construct(
        private readonly AssetStorageRepository $assetStorageRepository,
        private readonly AssetCollectionRepository $assetCollectionRepository,
        private readonly LoggerService $logger,
        private readonly EntityManagerInterface $entityManager,
    ) {}
    public function clearStorage(int $id, int $userId): void
    {
        $locations = $this->assetStorageRepository->getStorageData($id);
        $this->assetCollectionRepository->removeCollection($locations);

        $this->logger->adminAction($userId, '', 'clear_storage', "$id");
    }

    public function clearLocation(int $location, int $userId): void
    {
        $em = $this->entityManager->getRepository(AssetCollection::class);
        if ($collectionLocation = $em->findOneBy(['id' => $location])) {
            $em->remove($collectionLocation, true);
            $this->logger->adminAction($userId, '', 'clear_location', $location);
        }
    }
}
