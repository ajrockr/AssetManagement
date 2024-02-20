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
     * @param int $id
     * @param string $issue
     * @param ArrayCollection $partsNeeded
     * @param string $status
     * @param int|null $submittedByUserId
     * @return void
     * @throws NonUniqueResultException
     */
    public function createOrUpdateRepair(int $assetId, string $issue, ArrayCollection $partsNeeded, string $status = 'not_started', ?int $submittedByUserId = null): void
    {
        // TODO I made the getRepair id in the repository find by assetId but updating repair from Repair seems weird now, idk...
        $repair = $this->repairRepository->getRepair($assetId, true);

        if ( !$repair) {
            $repair = new Repair;
            $repair->setCreatedDate(new \DateTimeImmutable('now'));
        }

        $repair->setIssue($issue)
            ->setAssetId($assetId)
            ->setStatus($status)
            ->setPartsNeeded($this->processPartsNeededForDb($partsNeeded))
            ->setLastModifiedDate(new \DateTimeImmutable('now'))
            ->setSubmittedById($submittedByUserId)
        ;

        $this->repairRepository->save($repair, true);
    }

    public function assignTechnician(int $repairId, int $userId): void
    {
        if ($repair = $this->repairRepository->findOneBy(['id' => $repairId])) {
            $repair->setTechnicianId($userId);
            $this->repairRepository->save($repair, true);
        }
    }

    public function unassignTechnician(int $repairId): void
    {
        if ($repair = $this->repairRepository->findOneBy(['id' => $repairId])) {
            $repair->setTechnicianId(null);
            $this->repairRepository->save($repair, true);
        }
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
