<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $username = null;

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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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
}
