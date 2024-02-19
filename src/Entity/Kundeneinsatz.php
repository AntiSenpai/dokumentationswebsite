<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Kundeneinsatz
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Zeiterfassung")
     * @ORM\JoinColumn(nullable=false)
     */
    private $zeiterfassungseintrag;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    // Getter und Setter Methoden

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getZeiterfassungseintrag(): ?Zeiterfassung
    {
        return $this->zeiterfassungseintrag;
    }

    public function setZeiterfassungseintrag(?Zeiterfassung $zeiterfassungseintrag): self
    {
        $this->zeiterfassungseintrag = $zeiterfassungseintrag;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }
}
