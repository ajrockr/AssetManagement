<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


// @todo Make avatar, allow setting from Google/Microsoft

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['username', 'email'], message: 'There is already an account with this username/email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $department = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $extension = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $homepage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $manager = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $microsoftId = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $dateCreated = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $surname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(type: "boolean", nullable: true, options: [
        "default" => true
    ])]
    private ?bool $enabled = true;

    #[ORM\Column(nullable: true)]
    private ?bool $pending = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userUniqueId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $lastActivity = null;

    /**
     * getId
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * getUsername
     *
     * @return string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * setUsername
     *
     * @param  mixed $username
     * @return self
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * setRoles
     *
     * @param  mixed $roles
     * @return self
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * setPassword
     *
     * @param  mixed $password
     * @return self
     */
    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * getEmail
     *
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * setEmail
     *
     * @param  mixed $email
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * getLocation
     *
     * @return string
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * setLocation
     *
     * @param  mixed $location
     * @return self
     */
    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * getDepartment
     *
     * @return string
     */
    public function getDepartment(): ?string
    {
        return $this->department;
    }

    /**
     * setDepartment
     *
     * @param  mixed $department
     * @return self
     */
    public function setDepartment(?string $department): self
    {
        $this->department = $department;

        return $this;
    }

    /**
     * getPhone
     *
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * setPhone
     *
     * @param  mixed $phone
     * @return self
     */
    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * getExtension
     *
     * @return string
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * setExtension
     *
     * @param  mixed $extension
     * @return self
     */
    public function setExtension(?string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * getTitle
     *
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * setTitle
     *
     * @param  mixed $title
     * @return self
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * getHomepage
     *
     * @return string
     */
    public function getHomepage(): ?string
    {
        return $this->homepage;
    }

    /**
     * setHomepage
     *
     * @param  mixed $homepage
     * @return self
     */
    public function setHomepage(?string $homepage): self
    {
        $this->homepage = $homepage;

        return $this;
    }

    /**
     * getManager
     *
     * @return string
     */
    public function getManager(): ?string
    {
        return $this->manager;
    }

    /**
     * setManager
     *
     * @param  mixed $manager
     * @return self
     */
    public function setManager(?string $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * getGoogleId
     *
     * @return string
     */
    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    /**
     * setGoogleId
     *
     * @param  mixed $googleId
     * @return self
     */
    public function setGoogleId(?string $googleId): self
    {
        $this->googleId = $googleId;

        return $this;
    }

    /**
     * getMicrosoftId
     *
     * @return string
     */
    public function getMicrosoftId(): ?string
    {
        return $this->microsoftId;
    }

    /**
     * setMicrosoftId
     *
     * @param  mixed $microsoftId
     * @return self
     */
    public function setMicrosoftId(?string $microsoftId): self
    {
        $this->microsoftId = $microsoftId;

        return $this;
    }

    /**
     * getDateCreated
     *
     * @return DateTimeImmutable
     */
    public function getDateCreated(): ?DateTimeImmutable
    {
        return $this->dateCreated;
    }

    /**
     * setDateCreated
     *
     * @param  mixed $dateCreated
     * @return self
     */
    public function setDateCreated(?DateTimeImmutable $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * getSurname
     *
     * @return string
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     * setSurname
     *
     * @param  mixed $surname
     * @return self
     */
    public function setSurname(?string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * getFirstname
     *
     * @return string
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * setFirstname
     *
     * @param  mixed $firstname
     * @return self
     */
    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * isEnabled
     *
     * @return bool
     */
    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * setEnabled
     *
     * @param  mixed $enabled
     * @return self
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * isPending
     *
     * @return bool
     */
    public function isPending(): ?bool
    {
        return $this->pending;
    }

    /**
     * setPending
     *
     * @param  mixed $pending
     * @return self
     */
    public function setPending(?bool $pending): self
    {
        $this->pending = $pending;

        return $this;
    }

    /**
     * getAvatar
     *
     * @return string
     */
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    /**
     * setAvatar
     *
     * @param  mixed $avatar
     * @return self
     */
    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * getUserUniqueId
     *
     * @return string
     */
    public function getUserUniqueId(): ?string
    {
        return $this->userUniqueId;
    }

    /**
     * setUserUniqueId
     *
     * @param  mixed $userUniqueId
     * @return self
     */
    public function setUserUniqueId(?string $userUniqueId): self
    {
        $this->userUniqueId = $userUniqueId;

        return $this;
    }

    /**
     * getType
     *
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * setType
     *
     * @param  mixed $type
     * @return self
     */
    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * getLastActivity
     *
     * @return DateTimeImmutable
     */
    public function getLastActivity(): ?DateTimeImmutable
    {
        return $this->lastActivity;
    }

    /**
     * setLastActivity
     *
     * @param  mixed $lastActivity
     * @return self
     */
    public function setLastActivity(?DateTimeImmutable $lastActivity): self
    {
        $this->lastActivity = $lastActivity;

        return $this;
    }
}
