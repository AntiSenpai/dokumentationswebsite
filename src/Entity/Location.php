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
    private $adresse;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $istHauptstandort = false;

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Location $unterstandort = null;
    
    public function getId(): ?int {
        return $this->id;
    }

    public function getAdresse(): ?string {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self {
        $this->adresse = $adresse;
        return $this;
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
}
