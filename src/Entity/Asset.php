<?php

namespace App\Entity;

use App\Repository\AssetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: AssetRepository::class)]
#[UniqueEntity(fields: 'serialnumber', message: 'There is already an account with this serial number')]
#[UniqueEntity(fields: 'assettag', message: 'There is already an account with this asset tag')]
class Asset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $serial_number = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $asset_tag = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $purchase_date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $purchased_from = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $warranty_start_date = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $warranty_end_date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $asset_condition = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $make = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $model = null;

    #[ORM\Column(nullable: true)]
    private ?bool $decommissioned = null;

    #[ORM\Column(nullable: true)]
    private ?int $assigned_to = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param ExecutionContextInterface $context
     * @param $payload
     * @return void
     */
    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context, $payload): void
    {
        if (null !== $this->getWarrantystartdate()) {
            if ($this->getWarrantyenddate() <= $this->getWarrantystartdate()) {
                $context->buildViolation('Warranty End Date must not be older than the start date.')
                    ->atPath('warranty_end_date')
                    ->addViolation();
            }
        }
    }
    public function getSerialNumber(): ?string
    {
        return $this->serial_number;
    }

    public function setSerialNumber(?string $serial_number): self
    {
        $this->serial_number = $serial_number;

        return $this;
    }

    public function getAssetTag(): ?string
    {
        return $this->asset_tag;
    }

    public function setAssetTag(?string $asset_tag): self
    {
        $this->asset_tag = $asset_tag;

        return $this;
    }

    public function getPurchaseDate(): ?\DateTimeImmutable
    {
        return $this->purchase_date;
    }

    public function setPurchaseDate(?\DateTimeImmutable $purchase_date): self
    {
        $this->purchase_date = $purchase_date;

        return $this;
    }

    public function getPurchasedFrom(): ?string
    {
        return $this->purchased_from;
    }

    public function setPurchasedFrom(?string $purchased_from): self
    {
        $this->purchased_from = $purchased_from;

        return $this;
    }

    public function getWarrantyStartDate(): ?\DateTimeImmutable
    {
        return $this->warranty_start_date;
    }

    public function setWarrantyStartDate(?\DateTimeImmutable $warranty_start_date): self
    {
        $this->warranty_start_date = $warranty_start_date;

        return $this;
    }

    public function getWarrantyEndDate(): ?\DateTimeImmutable
    {
        return $this->warranty_end_date;
    }

    public function setWarrantyEndDate(?\DateTimeImmutable $warranty_end_date): self
    {
        $this->warranty_end_date = $warranty_end_date;

        return $this;
    }

    public function getAssetCondition(): ?string
    {
        return $this->asset_condition;
    }

    public function setAssetCondition(?string $condition): self
    {
        $this->asset_condition = $condition;

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

    public function isDecommissioned(): ?bool
    {
        return $this->decommissioned;
    }

    public function setDecommissioned(?bool $decommissioned): self
    {
        $this->decommissioned = $decommissioned;

        return $this;
    }

    public function getAssignedTo(): ?int
    {
        return $this->assigned_to;
    }

    public function setAssignedTo(?int $assigned_to): self
    {
        $this->assigned_to = $assigned_to;

        return $this;
    }
}
