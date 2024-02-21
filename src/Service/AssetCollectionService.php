<?php

namespace App\Service;

use App\Entity\Asset;
use App\Entity\AssetCollection;
use App\Entity\User;
use App\Repository\AssetCollectionRepository;
use App\Repository\AssetRepository;
use App\Repository\AssetStorageRepository;
use App\Repository\RepairRepository;
use App\Repository\SiteConfigRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssetCollectionService
{
    public function __construct(
        protected readonly EventDispatcherInterface  $eventDispatcher,
        protected readonly EntityManagerInterface    $entityManager,
        protected readonly LoggerService             $logger,
        protected readonly AssetCollectionRepository $assetCollectionRepository,
        protected readonly AssetStorageRepository    $assetStorageRepository,
        protected readonly AssetRepository           $assetRepository,
        protected readonly SiteConfigRepository      $siteConfigRepository,
        protected readonly RepairRepository          $repairRepository,
        protected readonly RepairService             $repairService,
        protected readonly UserRepository            $userRepository,
    ) {}

    /**
     * @throws NonUniqueResultException
     */
    public function checkIn(array $data, int $userId): ?int
    {
        $configForceAssignUser = $this->siteConfigRepository->findOneByName('asset_assignUser_on_checkin');

        $asset = $this->createOrUpdateAsset($data);

        // Storage Name
        $storageName = $this->assetStorageRepository->findOneBy(['id' => $data['storageId']])->getname();

        // If the location is not already in use or collectOtherLocation is set, make a new collection
        if ( !($assetCollection = $this->assetCollectionRepository->findOneBy(['collectionLocation' => $data['location']]))
            || array_key_exists('collectOtherLocation', $data)) {
            $assetCollection = new AssetCollection();
        }
        $assetCollection->setCollectedDate(new \DateTimeImmutable('now'))
            ->setCollectedBy($userId)
            ->setCollectionLocation($data['location'])
            ->setDeviceID($asset->getId())
            ->setCollectedFrom($data['assigned_to'])
            ->setCheckedout($data['check_out'] ?? false)
            ->setProcessed($data['processed'] ?? false)
            ->setCollectionNotes($data['notes'] ?? null)
            ->setCollectionStorage($data['storageId'])
        ;

        // If the asset is not assigned or the config value to overwrite the assigned user is true, overwrite the assigned user.
        if (null === $asset->getAssignedTo() || $configForceAssignUser) {
            // TODO Make this a repository function
            $asset->setAssignedTo($data['assigned_to']);
            $this->assetRepository->save($asset, true);
        }

        // Check if Repair is needed
        if (array_key_exists('needs_repair', $data)) {
            if ($data['needs_repair']) {
                // TODO Do I need the array_key_exists and bool conditional?
                $this->repairService->createOrUpdateRepair($asset->getId(), $data['notes'] ?? 'Issue not listed', $data['repairPartsNeeded'], submittedByUserId:  $userId);
            }
        }

        // Persist the collection record
        $this->assetCollectionRepository->save($assetCollection, true);

        $this->logger->assetCheckInOut($userId,
            $asset->getId(),
            $this->logger::ACTION_ASSET_CHECKIN,
            'asset_collection',
            $storageName,
            $data['location'],
            $data['assigned_to']
        );

        return $assetCollection->getId();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function createOrUpdateAsset(array $data)
    {
        $asset = $this->assetRepository->findByAssetId($data['asset_tag'], $data['serial_number'] ?? null);
        if (null === $asset) {
            $asset = new Asset;
        }

        $asset->setAssetCondition($data['condition'] ?? null)
            ->setAssetTag($data['asset_tag'] ?? null)
            ->setSerialNumber($data['serial_number'] ?? null)
            ->setMake($data['make'] ?? null)
            ->setModel($data['model'] ?? null)
            ->setAssignedTo($data['assigned_to'] ?? null)
            ->SetDecommissioned($data['decommissioned'] ??null)
            ->setPurchaseDate($data['purchased_date'] ?? null)
            ->setPurchasedFrom($data['purchased_from'] ?? null)
            ->setWarrantyStartDate($data['warranty_start_date'] ?? null)
            ->setWarrantyEndDate($data['warranty_end_date'] ?? null)
        ;

        $this->entityManager->persist($asset);
        $this->entityManager->flush();

        return $asset;
    }

    public function assetIsCollected(int $assetId): ?AssetCollection
    {
        return $this->assetCollectionRepository->findOneBy(['DeviceID' => $assetId]);
    }

    /**
     * Checks that a user set in collected_from still exists in the database.
     * 
     * @param int $collectionId
     * @return User|null
     */
    public function getCollectedFrom(int $collectionId): ?User
    {
        $collection = $this->assetCollectionRepository->findOneBy(['id' => $collectionId]);
        if ($collection) {
            if ($user = $this->userRepository->findOneBy(['id' => $collection->getCollectedFrom()])) {
                return $user;
            }

        }

        return null;
    }
}
