<?php

namespace App\Entity;

use App\Repository\SimulationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: SimulationRepository::class)]
class Simulation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $duration_use = null;

    #[ORM\Column]
    private ?int $nbr_use = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $hour_use = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: "id_product", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?Product $product = null;
    
    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: "id_client", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?Client $client = null;
    
     #[ORM\OneToMany(mappedBy: "simulation", targetEntity: EnergyBill::class, cascade: ["remove"])]
     private Collection $energyBills;
 
     public function __construct()
     {
         $this->energyBills = new ArrayCollection();
     }

     public function getEnergyBills(): Collection
    {
        return $this->energyBills;
    }
    public function addEnergyBill(EnergyBill $energyBill): static
    {
        if (!$this->energyBills->contains($energyBill)) {
            $this->energyBills[] = $energyBill;
            $energyBill->setSimulation($this);
        }
    
        return $this;
    }
 
    public function getIdClient(): ?Client
    {
        return $this->client;
    }

    public function setIdClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDurationUse(): ?int
    {
        return $this->duration_use;
    }

    public function setDurationUse(int $duration_use): static
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
