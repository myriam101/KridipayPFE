<?php

namespace App\Entity;

use App\Repository\PriceElectricityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Entity\Enum\PeriodeUse;
use App\Entity\Enum\Sector;
use App\Entity\Enum\TrancheElect;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PriceElectricityRepository::class)]
class PriceElectricity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, enumType: Sector::class)] 
    #[Groups(['elect:read'])]
    private Sector $sector;

    #[ORM\Column(type: Types::STRING, enumType: TrancheElect::class)] 
    #[Groups(['elect:read'])]
    private TrancheElect $tranche_elect;

    #[ORM\Column]
    #[Groups(['elect:read'])]
    private ?float $price = null;

    public function getId(): ?int
    {
        return $this->id;
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
     public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }
   
}
