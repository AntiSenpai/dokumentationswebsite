<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Es gibt bereits ein Konto mit dieser E-Mail-Adresse')]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
   
    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $confirmationToken;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column(length: 255)]
    private ?string $timer = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Mitarbeiter::class)]
    private Collection $mitarbeiters;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $totpSecret;

    #[ORM\Column(type: 'boolean')]
    private $isTotpEnabled = false;

    #[ORM\Column(type: 'boolean')]
    private $IsEmailVerificationEnabled = true;

    #[UploadableField(mapping: 'profile_image', fileNameProperty: 'profilePicture')]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $profilePicture = null;

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): static
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    public function isEmailVerificationEnabled(): bool
    {
        return $this->IsEmailVerificationEnabled;
    }

    public function setIsEmailVerificationEnabled(bool $IsEmailVerificationEnabled): static
    {
        $this->IsEmailVerificationEnabled = $IsEmailVerificationEnabled;

        return $this;
    }

    public function isTotpEnabled(): bool
    {
        return $this->isTotpEnabled;
    }

    public function setIsTotpEnabled(bool $isTotpEnabled): static
    {
        $this->isTotpEnabled = $isTotpEnabled;

        return $this;
    }

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(?string $totpSecret): static
    {
        $this->totpSecret = $totpSecret;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): static
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function __construct()
    {
        $this->mitarbeiters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // Jeder USER kriegt dadurch automatisch die Rolle "ROLE_USER"
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getTimer(): ?string
    {
        return $this->timer;
    }

    public function setTimer(string $timer): static
    {
        $this->timer = $timer;

        return $this;
    }

    /**
     * @return Collection<int, Mitarbeiter>
     */
    public function getMitarbeiters(): Collection
    {
        return $this->mitarbeiters;
    }

    public function addMitarbeiter(Mitarbeiter $mitarbeiter): static
    {
        if (!$this->mitarbeiters->contains($mitarbeiter)) {
            $this->mitarbeiters->add($mitarbeiter);
            $mitarbeiter->setUser($this);
        }

        return $this;
    }

    public function removeMitarbeiter(Mitarbeiter $mitarbeiter): static
    {
        if ($this->mitarbeiters->removeElement($mitarbeiter)) {
            // set the owning side to null (unless already changed)
            if ($mitarbeiter->getUser() === $this) {
                $mitarbeiter->setUser(null);
            }
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
