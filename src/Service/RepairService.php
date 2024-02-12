<?php

namespace App\Service;

use App\Entity\Repair;
use App\Repository\RepairRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\AssetRepository;

class RepairService
{
    public function __construct(
        protected readonly AssetRepository $assetRepository,
        protected readonly RepairRepository $repairRepository,
    ) {}

    /**
     * @param int $uid
     * @param string $issue
     * @param ArrayCollection $partsNeeded
     * @param int $submittedByUserId
     * @return void
     * @throws NonUniqueResultException
     */
    public function createOrUpdateRepair(int $uid, string $issue, ArrayCollection $partsNeeded, int $submittedByUserId): void
    {
        if ( !($repair = $this->repairRepository->getRepair($uid, true))) {
            $repair = new Repair;
        }
        $repair->setIssue($issue)
            ->setAssetId($uid)
            ->setStatus('Not Started')
            ->setPartsNeeded($this->processPartsNeededForDb($partsNeeded))
            ->setCreatedDate(new \DateTimeImmutable('now'))
            ->setLastModifiedDate(new \DateTimeImmutable('now'))
            ->setSubmittedById($submittedByUserId)
        ;

        $this->repairRepository->save($repair, true);
    }

    /**
     * @param ArrayCollection $partsNeeded
     * @return array
     */
    private function processPartsNeededForDb(ArrayCollection $partsNeeded): array
    {
        $array = $partsNeeded->toArray();
        $parts = [];
        foreach ($array as $part) {
            $parts[] = [
                'name' => $part->getName(),
                'id' => $part->getId(),
            ];
        }

        return $parts;
    }
}
