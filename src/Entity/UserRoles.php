<?php

namespace App\Entity;

use App\Repository\UserRolesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRolesRepository::class)]
class UserRoles
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $roleName = null;

    #[ORM\Column(length: 255)]
    private ?string $roleValue = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $roleDescription = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    public function setRoleName(string $roleName): self
    {
        $this->roleName = $roleName;

        return $this;
    }

    public function getRoleValue(): ?string
    {
        return $this->roleValue;
    }

    public function setRoleValue(string $roleValue): self
    {
        $this->roleValue = $roleValue;

        return $this;
    }

    public function getRoleDescription(): ?string
    {
        return $this->roleDescription;
    }

    public function setRoleDescription(?string $roleDescription): self
    {
        $this->roleDescription = $roleDescription;

        return $this;
    }
}
