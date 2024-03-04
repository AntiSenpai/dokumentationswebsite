<?php
namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Location::class)]
    private $locations;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $CreatedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $suchnummer = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $UpdatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $UpdatedBy = null;

    #[ORM\OneToMany(mappedBy: 'customerId', targetEntity: Location::class)]
    private Collection $customerId;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $technischerAnsprechpartner = null;

    #[ORM\Column(length: 255)]
    private ?string $vorOrtAnsprechpartner = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    private ?float $stundensatz = null;

    public function __construct()
    {
       $this->locations = new ArrayCollection();
    }

    public function getStundensatz(): ?float {
        return $this->stundensatz;
    }

    public function setStundensatz(float $stundensatz): self {
        $this->stundensatz = $stundensatz;
        return $this;
    }

    public function getTechnischerAnsprechpartner(): ?User {
        return $this->technischerAnsprechpartner;
    }

    public function setTechnischerAnsprechpartner(?User $technischerAnsprechpartner): self {
        $this->technischerAnsprechpartner = $technischerAnsprechpartner;
        return $this;
    }

    public function getVorOrtAnsprechpartner(): ?string {
        return $this->vorOrtAnsprechpartner;
    }

    public function setVorOrtAnsprechpartner(string $vorOrtAnsprechpartner): self {
        $this->vorOrtAnsprechpartner = $vorOrtAnsprechpartner;
        return $this;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(string $email): self {
        $this->email = $email;
        return $this;
    }

    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getSuchnummer(): ?string {
        return $this->suchnummer;
    }

    public function setSuchnummer(string $suchnummer): self {
        $this->suchnummer = $suchnummer;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface {
        return $this->UpdatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $UpdatedAt): self {
        $this->UpdatedAt = $UpdatedAt;
        return $this;
    }

    public function getUpdatedBy(): ?User {
        return $this->UpdatedBy;
    }

    public function setUpdatedBy(?User $UpdatedBy): self {
        $this->UpdatedBy = $UpdatedBy;
        return $this;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface {
        return $this->CreatedAt;
    }

    public function setCreatedAt(?\DateTimeInterface $CreatedAt): self {
        $this->CreatedAt = $CreatedAt;
        return $this;
    }

    /**
     * @return Collection<int, Location>
     */
    public function getCustomerId(): Collection
    {
        return $this->customerId;
    }

    public function addCustomerId(Location $customerId): static
    {
        if (!$this->customerId->contains($customerId)) {
            $this->customerId->add($customerId);
            $customerId->setCustomerId($this);
        }

        return $this;
    }

    public function removeCustomerId(Location $customerId): static
    {
        if ($this->customerId->removeElement($customerId)) {
            // set the owning side to null (unless already changed)
            if ($customerId->getCustomerId() === $this) {
                $customerId->setCustomerId(null);
            }
        }

        return $this;
    }

}
