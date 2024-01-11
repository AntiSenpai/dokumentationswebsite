<?php

namespace App\Entity;

use App\Repository\ClientsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientsRepository::class)]
class Clients
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Hersteller = null;

    #[ORM\Column(length: 255)]
    private ?string $DHCP = null;

    #[ORM\Column(length: 255)]
    private ?string $AnzahlAlter = null;

    #[ORM\Column(length: 255)]
    private ?string $Sonstiges = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHersteller(): ?string
    {
        return $this->Hersteller;
    }

    public function setHersteller(string $Hersteller): static
    {
        $this->Hersteller = $Hersteller;

        return $this;
    }

    public function getDHCP(): ?string
    {
        return $this->DHCP;
    }

    public function setDHCP(string $DHCP): static
    {
        $this->DHCP = $DHCP;

        return $this;
    }

    public function getAnzahlAlter(): ?string
    {
        return $this->AnzahlAlter;
    }

    public function setAnzahlAlter(string $AnzahlAlter): static
    {
        $this->AnzahlAlter = $AnzahlAlter;

        return $this;
    }

    public function getSonstiges(): ?string
    {
        return $this->Sonstiges;
    }

    public function setSonstiges(string $Sonstiges): static
    {
        $this->Sonstiges = $Sonstiges;

        return $this;
    }
}
