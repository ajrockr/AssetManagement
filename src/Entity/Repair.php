<?php

namespace App\Entity;

use App\Repository\RepairRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RepairRepository::class)]
class Repair
{
    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';

    public const STATUS_WAITING_ON_PARTS = 'waiting_on_parts';
    public const STATUS_STARTED = 'started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DELAYED = 'delayed';
    public const STATUS_DEFERRED = 'deferred';
    public const STATUS_WAITING_ON_TECHNICIAN = 'waiting_on_technician';
    public const STATUS_WAITING_ON_USER = 'waiting_on_user';
    public const STATUS_CUSTOM = 'custom';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $assetId = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startedDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resolvedDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $technicianId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $issue = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $partsNeeded = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $actionsPerformed = [];

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $lastModifiedDate = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $usersFollowing = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $assetUniqueIdentifier = null;

    #[ORM\ManyToOne]
    private ?User $techId = null;

    #[ORM\Column(nullable: true)]
    private ?int $submittedById = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAssetId(): ?int
    {
        return $this->assetId;
    }

    public function setAssetId(int $assetId): self
    {
        $this->assetId = $assetId;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeImmutable
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeImmutable $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getStartedDate(): ?\DateTimeImmutable
    {
        return $this->startedDate;
    }

    public function setStartedDate(?\DateTimeImmutable $startedDate): self
    {
        $this->startedDate = $startedDate;

        return $this;
    }

    public function getResolvedDate(): ?\DateTimeImmutable
    {
        return $this->resolvedDate;
    }

    public function setResolvedDate(?\DateTimeImmutable $resolvedDate): self
    {
        $this->resolvedDate = $resolvedDate;

        return $this;
    }

    public function getTechnicianId(): ?int
    {
        return $this->technicianId;
    }

    public function setTechnicianId(?int $technicianId): self
    {
        $this->technicianId = $technicianId;

        return $this;
    }

    public function getIssue(): ?string
    {
        return $this->issue;
    }

    public function setIssue(?string $issue): self
    {
        $this->issue = $issue;

        return $this;
    }

    public function getPartsNeeded(): array
    {
        return $this->partsNeeded;
    }

    public function setPartsNeeded(?array $partsNeeded): self
    {
        $this->partsNeeded = $partsNeeded;

        return $this;
    }

    public function getActionsPerformed(): array
    {
        return $this->actionsPerformed;
    }

    public function setActionsPerformed(?array $actionsPerformed): self
    {
        $this->actionsPerformed = $actionsPerformed;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLastModifiedDate(): ?\DateTimeImmutable
    {
        return $this->lastModifiedDate;
    }

    public function setLastModifiedDate(\DateTimeImmutable $lastModifiedDate): self
    {
        $this->lastModifiedDate = $lastModifiedDate;

        return $this;
    }

    public function getUsersFollowing(): array
    {
        return $this->usersFollowing;
    }

    public function setUsersFollowing(?array $usersFollowing): self
    {
        $this->usersFollowing = $usersFollowing;

        return $this;
    }

    public function getAssetUniqueIdentifier(): ?string
    {
        return $this->assetUniqueIdentifier;
    }

    public function setAssetUniqueIdentifier(string $assetUniqueIdentifier): self
    {
        $this->assetUniqueIdentifier = $assetUniqueIdentifier;

        return $this;
    }

    public function getTechId(): ?User
    {
        return $this->techId;
    }

    public function setTechId(?User $techId): self
    {
        $this->techId = $techId;

        return $this;
    }

    public function getSubmittedById(): ?int
    {
        return $this->submittedById;
    }

    public function setSubmittedById(?int $submittedById): static
    {
        $this->submittedById = $submittedById;

        return $this;
    }
}
