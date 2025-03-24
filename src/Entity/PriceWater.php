<?php

namespace App\Entity;

use App\Repository\PriceWaterRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;


#[ORM\Entity(repositoryClass: PriceWaterRepository::class)]
class PriceWater
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(type: Types::STRING, enumType: TrancheEau::class)] 
    private TrancheEau $tranche_eau;

    #[ORM\OneToOne(targetEntity: EnergyBill::class, mappedBy: "priceWater")]
    private ?EnergyBill $energyBill = null;

    public function getId(): ?int
    {
        return $this->id;
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
    public function getTrancheeau(): TrancheEau
    {
        return $this->tranche_eau;
    }

    public function setTrancheeau(TrancheEau $tranche_eau): self
    {
        $this->tranche_eau = $tranche_eau;
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
