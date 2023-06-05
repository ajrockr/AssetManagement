<?php

namespace App\Entity;

use App\Repository\VendorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VendorRepository::class)]
class Vendor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $primaryContactName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $primaryContactPhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $primaryContactEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPhone1(): ?string
    {
        return $this->phone1;
    }

    public function setPhone1(?string $phone1): self
    {
        $this->phone1 = $phone1;

        return $this;
    }

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function setPhone2(?string $phone2): self
    {
        $this->phone2 = $phone2;

        return $this;
    }

    public function getPrimaryContactName(): ?string
    {
        return $this->primaryContactName;
    }

    public function setPrimaryContactName(?string $primaryContactName): self
    {
        $this->primaryContactName = $primaryContactName;

        return $this;
    }

    public function getPrimaryContactPhone(): ?string
    {
        return $this->primaryContactPhone;
    }

    public function setPrimaryContactPhone(?string $primaryContactPhone): self
    {
        $this->primaryContactPhone = $primaryContactPhone;

        return $this;
    }

    public function getPrimaryContactEmail(): ?string
    {
        return $this->primaryContactEmail;
    }

    public function setPrimaryContactEmail(?string $primaryContactEmail): self
    {
        $this->primaryContactEmail = $primaryContactEmail;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }
}
