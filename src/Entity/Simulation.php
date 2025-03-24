<?php

namespace App\Entity;

use App\Repository\SimulationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SimulationRepository::class)]
class Simulation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $duration_use = null;

    #[ORM\Column]
    private ?int $nbr_use = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $hour_use = null;

    #[ORM\OneToOne(targetEntity: Product::class, inversedBy: "simulation")]
    #[ORM\JoinColumn(name: "id_product", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?Product $product = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDurationUse(): ?\DateTimeInterface
    {
        return $this->duration_use;
    }

    public function setDurationUse(\DateTimeInterface $duration_use): static
    {
        $this->duration_use = $duration_use;

        return $this;
    }

    public function getNbrUse(): ?int
    {
        return $this->nbr_use;
    }

    public function setNbrUse(int $nbr_use): static
    {
        $this->nbr_use = $nbr_use;

        return $this;
    }

    public function getHourUse(): ?\DateTimeInterface
    {
        return $this->hour_use;
    }

    public function setHourUse(\DateTimeInterface $hour_use): static
    {
        $this->hour_use = $hour_use;

        return $this;
    }
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): static
    {
        $this->product = $product;
        return $this;
    }
}
