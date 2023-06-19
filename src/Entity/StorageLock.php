<?php

namespace App\Entity;

use App\Repository\StorageLockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StorageLockRepository::class)]
class StorageLock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $storageId = null;

    #[ORM\Column]
    private ?bool $locked = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStorageId(): ?int
    {
        return $this->storageId;
    }

    public function setStorageId(int $storageId): self
    {
        $this->storageId = $storageId;

        return $this;
    }

    public function isLocked(): ?bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }
}
