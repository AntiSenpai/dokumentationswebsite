<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Repository\LocationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $adresse;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $beschreibung = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $istHauptstandort = false;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Location $unterstandort = null;

    #[ORM\ManyToOne(inversedBy: 'customerId')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customerId = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    // ... Getter und Setter für jede Eigenschaft ...

    public function getId(): ?int {
        return $this->id;
    }

    public function getCustomer(): ?Customer {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self {
        $this->customer = $customer;
        return $this;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function getAdresse(): ?string {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self {
        $this->adresse = $adresse;
        return $this;
    }

    public function getBeschreibung(): ?string {
        return $this->beschreibung;
    }

    public function setBeschreibung(?string $beschreibung): self {
        $this->beschreibung = $beschreibung;
        return $this;
    }

    public function isIstHauptstandort(): ?bool {
        return $this->istHauptstandort;
    }

    public function setIstHauptstandort(bool $istHauptstandort): self {
        $this->istHauptstandort = $istHauptstandort;
        return $this;
    }

    public function getUnterstandort(): ?self {
        return $this->unterstandort;
    }

    public function setUnterstandort(?self $unterstandort): self {
        $this->unterstandort = $unterstandort;
        return $this;
    }

    public function getCustomerId(): ?Customer
    {
        return $this->customerId;
    }

    public function setCustomerId(?Customer $customerId): static
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

}

