<?php

namespace App\Entity;

use App\Repository\PriceGazRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Entity\Enum\TrancheGaz;

#[ORM\Entity(repositoryClass: PriceGazRepository::class)]
class PriceGaz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $pressure = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(type: Types::STRING, enumType: TrancheGaz::class)] 
    private TrancheGaz $tranche_gaz;

    // OneToOne relationship with EnergyBill
    #[ORM\OneToOne(targetEntity: EnergyBill::class, mappedBy: "priceGaz")]
    private ?EnergyBill $energyBill = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isPressure(): ?bool
    {
        return $this->pressure;
    }

    public function setPressure(bool $pressure): static
    {
        $this->pressure = $pressure;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }
    public function getTrancheGaz(): TrancheGaz
    {
        return $this->tranche_gaz;
    }

    public function setTrancheGaz(TrancheGaz $tranche_gaz): self
    {
        $this->tranche_gaz = $tranche_gaz;
        return $this;
    }
    public function getEnergyBill(): ?EnergyBill
    {
        return $this->energyBill;
    }

    public function setEnergyBill(?EnergyBill $energyBill): static
    {
        $this->energyBill = $energyBill;
        return $this;
    }
}
