<?php

namespace App\Entity;

use App\Repository\ZeiterfassungRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ZeiterfassungRepository::class)]
class Zeiterfassung
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $typ = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startzeitpunkt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endzeitpunkt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTyp(): ?string
    {
        return $this->typ;
    }

    public function setTyp(string $typ): static
    {
        $this->typ = $typ;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStartzeitpunkt(): ?\DateTimeInterface
    {
        return $this->startzeitpunkt;
    }

    public function setStartzeitpunkt(\DateTimeInterface $startzeitpunkt): static
    {
        $this->startzeitpunkt = $startzeitpunkt;

        return $this;
    }

    public function getEndzeitpunkt(): ?\DateTimeInterface
    {
        return $this->endzeitpunkt;
    }

    public function setEndzeitpunkt(\DateTimeInterface $endzeitpunkt): static
    {
        $this->endzeitpunkt = $endzeitpunkt;

        return $this;
    }
}
