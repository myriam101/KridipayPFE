<?php

namespace App\Entity;

use App\Repository\PriceWaterRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Entity\Enum\TrancheEau;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: PriceWaterRepository::class)]
class PriceWater
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['water:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['water:read'])]
    private ?float $price = null;

    #[ORM\Column(type: Types::STRING, enumType: TrancheEau::class)] 
    #[Groups(['water:read'])]
    private TrancheEau $tranche_eau;

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
  
}
