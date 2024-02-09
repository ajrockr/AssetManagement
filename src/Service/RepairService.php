<?php

namespace App\Service;

use App\Entity\Repair;
use App\Repository\RepairRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\AssetRepository;

class RepairService
{
    public function __construct(
        protected readonly AssetRepository $assetRepository,
        protected readonly RepairRepository $repairRepository,
    ) {}
    #[IsGranted('ROLE_ASSET_REPAIR_MODIFY')]
    public function createRepair(array $repair): void
    {
        $repair = new Repair;
        $repair->setAssetUniqueIdentifier($repair['asset']);
        $repair->setIssue($repair['issue']);
        $repair->setAssetId($repair['assetId']);
        $repair->setStatus('Not Started');
        $repair->setPartsNeeded($repair['partsNeeded']);
        $repair->setCreatedDate(new \DateTimeImmutable('now'));
        $repair->setLastModifiedDate(new \DateTimeImmutable('now'));

        $this->repairRepository->save($repair, true);
    }
}
