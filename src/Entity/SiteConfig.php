<?php

namespace App\Entity;

use App\Repository\SiteConfigRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteConfigRepository::class)]
class SiteConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $configName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $configValue = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $configDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $defaultValue = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConfigName(): ?string
    {
        return $this->configName;
    }

    public function setConfigName(string $configName): self
    {
        $this->configName = $configName;

        return $this;
    }

    public function getConfigValue(): ?string
    {
        return $this->configValue;
    }

    public function setConfigValue(?string $configValue): self
    {
        $this->configValue = $configValue;

        return $this;
    }

    public function getConfigDescription(): ?string
    {
        return $this->configDescription;
    }

    public function setConfigDescription(?string $configDescription): self
    {
        $this->configDescription = $configDescription;

        return $this;
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(?string $defaultValue): self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }
}
