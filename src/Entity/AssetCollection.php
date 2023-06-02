<?php

namespace App\Entity;

use App\Repository\AssetCollectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssetCollectionRepository::class)]
class AssetCollection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $DeviceID = null;

    #[ORM\Column]
    private ?int $CollectedFrom = null;

    #[ORM\Column]
    private ?int $CollectedBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $collectedDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $collectionNotes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $collectionLocation = null;

    #[ORM\Column]
    private ?bool $checkedout = null;

    #[ORM\Column]
    private ?bool $processed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeviceID(): ?int
    {
        return $this->DeviceID;
    }

    public function setDeviceID(int $DeviceID): self
    {
        $this->DeviceID = $DeviceID;

        return $this;
    }

    public function getCollectedFrom(): ?int
    {
        return $this->CollectedFrom;
    }

    public function setCollectedFrom(int $CollectedFrom): self
    {
        $this->CollectedFrom = $CollectedFrom;

        return $this;
    }

    public function getCollectedBy(): ?int
    {
        return $this->CollectedBy;
    }

    public function setCollectedBy(int $CollectedBy): self
    {
        $this->CollectedBy = $CollectedBy;

        return $this;
    }

    public function getCollectedDate(): ?\DateTimeImmutable
    {
        return $this->collectedDate;
    }

    public function setCollectedDate(\DateTimeImmutable $collectedDate): self
    {
        $this->collectedDate = $collectedDate;

        return $this;
    }

    public function getCollectionNotes(): ?string
    {
        return $this->collectionNotes;
    }

    public function setCollectionNotes(?string $collectionNotes): self
    {
        $this->collectionNotes = $collectionNotes;

        return $this;
    }

    public function getCollectionLocation(): ?string
    {
        return $this->collectionLocation;
    }

    public function setCollectionLocation(?string $collectionLocation): self
    {
        $this->collectionLocation = $collectionLocation;

        return $this;
    }

    public function isCheckedout(): ?bool
    {
        return $this->checkedout;
    }

    public function setCheckedout(bool $checkedout): self
    {
        $this->checkedout = $checkedout;

        return $this;
    }

    public function isProcessed(): ?bool
    {
        return $this->processed;
    }

    public function setProcessed(bool $processed): self
    {
        $this->processed = $processed;

        return $this;
    }
}
