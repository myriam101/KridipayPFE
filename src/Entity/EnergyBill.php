<?php

namespace App\Entity;

use App\Repository\EnergyBillRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: EnergyBillRepository::class)]
class EnergyBill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $amount_bill = null;

    #[ORM\Column]
    private ?float $amount_gaz = null;

    #[ORM\Column]
    private ?float $amount_electr = null;

    #[ORM\Column]
    private ?float $amount_water = null;

    #[ORM\Column(type: Types::STRING, enumType: BillCategory::class)] 
    private BillCategory $bill_category;

    #[ORM\OneToOne(targetEntity: Product::class, inversedBy: "energyBill")]
    #[ORM\JoinColumn(name: "id_product", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?Product $product = null;

    #[ORM\OneToOne(targetEntity: PriceWater::class, inversedBy: "energyBill")]
    #[ORM\JoinColumn(name: "price_water_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?PriceWater $priceWater = null;
 
    // OneToOne relationship with PriceGaz
    #[ORM\OneToOne(targetEntity: PriceGaz::class, inversedBy: "energyBill")]
    #[ORM\JoinColumn(name: "price_gaz_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?PriceGaz $priceGaz = null;

    // OneToOne relationship with PriceElectricity
    #[ORM\OneToOne(targetEntity: PriceElectricity::class, inversedBy: "energyBill")]
    #[ORM\JoinColumn(name: "price_electricity_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?PriceElectricity $priceElectricity = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmountBill(): ?float
    {
        return $this->amount_bill;
    }

    public function setAmountBill(float $amount_bill): static
    {
        $this->amount_bill = $amount_bill;

        return $this;
    }

    public function getAmountGaz(): ?float
    {
        return $this->amount_gaz;
    }

    public function setAmountGaz(float $amount_gaz): static
    {
        $this->amount_gaz = $amount_gaz;

        return $this;
    }

    public function getAmountElectr(): ?float
    {
        return $this->amount_electr;
    }

    public function setAmountElectr(float $amount_electr): static
    {
        $this->amount_electr = $amount_electr;

        return $this;
    }

    public function getAmountWater(): ?float
    {
        return $this->amount_water;
    }

    public function setAmountWater(float $amount_water): static
    {
        $this->amount_water = $amount_water;

        return $this;
    }
    public function getBillcategory(): BillCategory
    {
        return $this->bill_category;
    }

    public function setBillcategory(BillCategory $bill_category): self
    {
        $this->bill_category = $bill_category;
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
    public function getPriceWater(): ?PriceWater
    {
        return $this->priceWater;
    }

    public function setPriceWater(?PriceWater $priceWater): static
    {
        $this->priceWater = $priceWater;
        return $this;
    }
    public function getPriceGaz(): ?PriceGaz
    {
        return $this->priceGaz;
    }

    public function setPriceGaz(?PriceGaz $priceGaz): static
    {
        $this->priceGaz = $priceGaz;
        return $this;
    }
    public function getPriceElectricity(): ?PriceElectricity
    {
        return $this->priceElectricity;
    }

    public function setPriceElectricity(?PriceElectricity $priceElectricity): static
    {
        $this->priceElectricity = $priceElectricity;
        return $this;
    }
}
