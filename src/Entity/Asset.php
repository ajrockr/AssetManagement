<?php

namespace App\Entity;

use App\Repository\AssetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssetRepository::class)]
class Asset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $serialnumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $assettag = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $purchasedate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $purchasedfrom = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $warrantystartdate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $warrantyenddate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $condition = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $make = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $model = null;

    #[ORM\Column(nullable: true)]
    private ?bool $decomisioned = null;

    #[ORM\Column(nullable: true)]
    private ?int $assignedTo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSerialnumber(): ?string
    {
        return $this->serialnumber;
    }

    public function setSerialnumber(?string $serialnumber): self
    {
        $this->serialnumber = $serialnumber;

        return $this;
    }

    public function getAssettag(): ?string
    {
        return $this->assettag;
    }

    public function setAssettag(?string $assettag): self
    {
        $this->assettag = $assettag;

        return $this;
    }

    public function getPurchasedate(): ?\DateTimeImmutable
    {
        return $this->purchasedate;
    }

    public function setPurchasedate(?\DateTimeImmutable $purchasedate): self
    {
        $this->purchasedate = $purchasedate;

        return $this;
    }

    public function getPurchasedfrom(): ?string
    {
        return $this->purchasedfrom;
    }

    public function setPurchasedfrom(?string $purchasedfrom): self
    {
        $this->purchasedfrom = $purchasedfrom;

        return $this;
    }

    public function getWarrantystartdate(): ?\DateTimeImmutable
    {
        return $this->warrantystartdate;
    }

    public function setWarrantystartdate(?\DateTimeImmutable $warrantystartdate): self
    {
        $this->warrantystartdate = $warrantystartdate;

        return $this;
    }

    public function getWarrantyenddate(): ?\DateTimeImmutable
    {
        return $this->warrantyenddate;
    }

    public function setWarrantyenddate(?\DateTimeImmutable $warrantyenddate): self
    {
        $this->warrantyenddate = $warrantyenddate;

        return $this;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition(?string $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function getMake(): ?string
    {
        return $this->make;
    }

    public function setMake(?string $make): self
    {
        $this->make = $make;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function isDecomisioned(): ?bool
    {
        return $this->decomisioned;
    }

    public function setDecomisioned(?bool $decomisioned): self
    {
        $this->decomisioned = $decomisioned;

        return $this;
    }

    public function getAssignedTo(): ?int
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?int $assignedTo): self
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }
}
