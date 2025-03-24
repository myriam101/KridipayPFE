<?php

namespace App\Entity;

use App\Enum\Sector;
use App\Enum\TrancheElect;
use App\Repository\PriceElectricityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: PriceElectricityRepository::class)]
class PriceElectricity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $tax = null;

    #[ORM\Column]
    private ?float $price_day = null;

    #[ORM\Column]
    private ?float $price_night = null;

    #[ORM\Column]
    private ?float $price_rush = null;

    #[ORM\Column(type: Types::STRING, enumType: PeriodeUse::class)] 
    private PeriodeUse $periode_use;

    #[ORM\Column(type: Types::STRING, enumType: Sector::class)] 
    private Sector $sector;

    #[ORM\Column(type: Types::STRING, enumType: TrancheElect::class)] 
    private TrancheElect $tranche_elect;

    
    // OneToOne relationship with EnergyBill
    #[ORM\OneToOne(targetEntity: EnergyBill::class, mappedBy: "priceElectricity")]
    private ?EnergyBill $energyBill = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTax(): ?float
    {
        return $this->tax;
    }

    public function setTax(float $tax): static
    {
        $this->tax = $tax;

        return $this;
    }

    public function getPriceDay(): ?float
    {
        return $this->price_day;
    }

    public function setPriceDay(float $price_day): static
    {
        $this->price_day = $price_day;

        return $this;
    }

    public function getPriceNight(): ?float
    {
        return $this->price_night;
    }

    public function setPriceNight(float $price_night): static
    {
        $this->price_night = $price_night;

        return $this;
    }

    public function getPriceRush(): ?float
    {
        return $this->price_rush;
    }

    public function setPriceRush(float $price_rush): static
    {
        $this->price_rush = $price_rush;

        return $this;
    }
    public function getPeriodeUse(): PeriodeUse
    {
        return $this->periode_use;
    }

    public function setPeriodeUse(PeriodeUse $periode_use): self
    {
        $this->periode_use = $periode_use;
        return $this;
    }
    public function getTrancheElect(): TrancheElect
    {
        return $this->tranche_elect;
    }

    public function setTrancheElect(TrancheElect $tranche_elect): self
    {
        $this->tranche_elect = $tranche_elect;
        return $this;
    }
    public function getSector(): Sector
    {
        return $this->sector;
    }

    public function setSector(Sector $sector): self
    {
        $this->sector = $sector;
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
