<?php

namespace App\Entity;

use App\Repository\PriceGazRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Entity\Enum\TrancheGaz;
use App\Entity\Enum\Sector;


#[ORM\Entity(repositoryClass: PriceGazRepository::class)]
class PriceGaz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(type: Types::STRING, enumType: TrancheGaz::class)] 
    private TrancheGaz $tranche_gaz;

    #[ORM\Column(type: Types::STRING, enumType: Sector::class)] 
    private Sector $sector;

    // OneToOne relationship with EnergyBill
    #[ORM\OneToOne(targetEntity: EnergyBill::class, mappedBy: "priceGaz")]
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
    public function getTrancheGaz(): TrancheGaz
    {
        return $this->tranche_gaz;
    }

    public function setTrancheGaz(TrancheGaz $tranche_gaz): self
    {
        $this->tranche_gaz = $tranche_gaz;
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
