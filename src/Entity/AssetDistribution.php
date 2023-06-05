<?php

namespace App\Entity;

use App\Repository\AssetDistributionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssetDistributionRepository::class)]
class AssetDistribution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $deviceId = null;

    #[ORM\Column(nullable: true)]
    private ?int $userId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $distributedAt = null;

    #[ORM\Column]
    private ?int $distributionSetBy = null;

    #[ORM\Column(nullable: true)]
    private ?int $distributedBy = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeviceId(): ?int
    {
        return $this->deviceId;
    }

    public function setDeviceId(int $deviceId): self
    {
        $this->deviceId = $deviceId;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDistributedAt(): ?\DateTimeImmutable
    {
        return $this->distributedAt;
    }

    public function setDistributedAt(?\DateTimeImmutable $distributedAt): self
    {
        $this->distributedAt = $distributedAt;

        return $this;
    }

    public function getDistributionSetBy(): ?int
    {
        return $this->distributionSetBy;
    }

    public function setDistributionSetBy(int $distributionSetBy): self
    {
        $this->distributionSetBy = $distributionSetBy;

        return $this;
    }

    public function getDistributedBy(): ?int
    {
        return $this->distributedBy;
    }

    public function setDistributedBy(?int $distributedBy): self
    {
        $this->distributedBy = $distributedBy;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }
}
