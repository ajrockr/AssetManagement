<?php

namespace App\Service;

use App\Entity\Asset;
use App\Entity\AssetCollection;
use App\Repository\AssetCollectionRepository;
use App\Repository\AssetRepository;
use App\Repository\AssetStorageRepository;
use App\Repository\RepairRepository;
use App\Repository\SiteConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;

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
    ) {}
    public function checkIn(array|Form $data, int $userId, array $neededParts = []): void
    {
        $configForceAssignUser = $this->siteConfigRepository->findOneByName('asset_assignUser_on_checkin');

        // TODO Need to change this, align all the data from the various ways of checking in an asset
        if ($data instanceof Form) {
            $data = $data->getData();
        }
//        if (is_array($data)) {
//            $data = [
//                'asset_tag' => $form['asset_tag'],
//                'serial_number' => $form['asset_serial'],
//                'assigned_to' => $form['assigned_to'],
//                'location' => $form['location'],
//                'storageId' => $form['storage'],
//                'checkout' => false,
//                'processed' => false,
//                'notes' => '',
//                'needs_repair' => false,
//            ];
//        } else {
//            $data = $form->getData();
//        }


        $asset = $this->createOrUpdateAsset($data);

        // Storage Name
        $storageName = $this->assetStorageRepository->findOneBy(['id' => $data['storageId']])->getname();

        if ( !($assetCollection = $this->assetCollectionRepository->findOneBy(['collectionLocation' => $data['location']]))) {
            // If the location is not already in use, make a new collection
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
                $repairData = [
                    'asset' => $data['device'],
                    'issue' => $data['notes'] ?? 'Issue not listed',
                    'assetId' => $asset->getId(),
                    'partsNeeded' => $neededParts
                ];
                $this->repairService->createRepair($repairData);
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
}
