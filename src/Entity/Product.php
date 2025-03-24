<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $short_description = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $brand = null;

    #[ORM\Column]
    private ?int $bonifpoint = null;

   #[ORM\ManyToOne(targetEntity:Category::class)]
   #[ORM\JoinColumn(name: 'id_category', referencedColumnName: 'id_category',nullable: true)]
   private ?Category $id_category =  null;

   
   #[ORM\OneToOne(targetEntity: Carbon::class, mappedBy: "product", cascade: ["persist", "remove"])]
   private ?Carbon $carbon = null;
   
   #[ORM\ManyToOne(targetEntity:Catalog::class)]
   #[ORM\JoinColumn(name: 'id_catalog', referencedColumnName: 'id_catalog',nullable: true)]
   private ?Catalog $id_catalog =  null;

   #[ORM\OneToOne(targetEntity: EnergyBill::class, mappedBy: "product", cascade: ["remove"])]
    private ?EnergyBill $energy_bill = null;

    #[ORM\OneToOne(targetEntity: Simulation::class, mappedBy: "product", cascade: ["remove"])]
    private ?Simulation $simulation = null;

    #[ORM\OneToOne(targetEntity: Feature::class, mappedBy: "product", cascade: ["remove"])]
    private ?Feature $feature = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->short_description;
    }

    public function setShortDescription(?string $short_description): static
    {
        $this->short_description = $short_description;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getBonifpoint(): ?int
    {
        return $this->bonifpoint;
    }

    public function setBonifpoint(int $bonifpoint): static
    {
        $this->bonifpoint = $bonifpoint;

        return $this;
    }
    public function getIdCategory(): ?Category
    {
        return $this->id_category;
    }

    public function setIdCategory(?Category $id_category): self
    {
        $this->id_category = $id_category;

        return $this;
    }
    public function getCarbon(): ?Carbon
    {
        return $this->carbon;
    }

    public function setCarbon(?Carbon $carbon): self
    {
        $this->carbon = $carbon;
        if ($carbon !== null && $carbon->getProduct() !== $this) {
            $carbon->setProduct($this);
        }
        return $this;
    }
    public function getIdCatalog(): ?Catalog
    {
        return $this->id_catalog;
    }

    public function setIdCatalog(?Catalog $id_catalog): self
    {
        $this->id_catalog = $id_catalog;

        return $this;
    }
    public function getEnergyBill(): ?EnergyBill
    {
        return $this->energy_bill;
    }

    public function setEnergyBill(?EnergyBill $energy_bill): static
    {
        $this->energy_bill = $energy_bill;
        if ($energy_bill !== null) {
            $energy_bill->setProduct($this);
        }
        return $this;
    }
    public function getSimulation(): ?Simulation
    {
        return $this->simulation;
    }

    public function setSimulation(?Simulation $simulation): static
    {
        $this->simulation = $simulation;
        if ($simulation !== null) {
            $simulation->setProduct($this);
        }
        return $this;
    }

    public function getFeature(): ?Feature
    {
        return $this->feature;
    }

    public function setFeature(?Feature $feature): static
    {
        $this->feature = $feature;
        if ($feature !== null) {
            $feature->setProduct($this);
        }
        return $this;
    }

}
