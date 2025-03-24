<?php

namespace App\Entity;

use App\Repository\FeatureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeatureRepository::class)]
class Feature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $weight = null;

    #[ORM\Column]
    private ?float $noise = null;

    #[ORM\Column]
    private ?float $power = null;

    #[ORM\Column]
    private ?float $consumption_liter = null;

    #[ORM\Column]
    private ?float $consumption_watt = null;

    #[ORM\Column]
    private ?float $hdr_consumption = null;

    #[ORM\Column]
    private ?float $sdr_consumption = null;

    #[ORM\Column]
    private ?float $capacity = null;

    #[ORM\Column]
    private ?float $dimension = null;

    #[ORM\Column]
    private ?float $volume_refrigeration = null;

    #[ORM\Column]
    private ?float $volume_freezer = null;

    #[ORM\Column]
    private ?float $volume_collect = null;

    #[ORM\Column]
    private ?float $seer = null;

    #[ORM\Column]
    private ?float $scop = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $cycle_duration = null;

    #[ORM\Column]
    private ?int $nbr_couvert = null;

    #[ORM\Column]
    private ?int $nb_bottle = null;

    #[ORM\Column]
    private ?float $resolution = null;

    #[ORM\Column]
    private ?float $diagonal = null;

    //classe energetique generale
    #[ORM\Column(type: Types::STRING, enumType: EnergyClass::class)] 
    private EnergyClass $energy_class;

   //performance condensation
    #[ORM\Column(type: Types::STRING, enumType: EnergyClass::class)] 
    private EnergyClass $condens_perform;

    //classe energetique essorage
    #[ORM\Column(type: Types::STRING, enumType: EnergyClass::class)] 
    private EnergyClass $spindry_class;
    
    //classe energetique vapeur
    #[ORM\Column(type: Types::STRING, enumType: EnergyClass::class)] 
    private EnergyClass $steam_class;

    //classe eclairage
    #[ORM\Column(type: Types::STRING, enumType: EnergyClass::class)] 
    private EnergyClass $light_class;

    //classe filtration
    #[ORM\Column(type: Types::STRING, enumType: EnergyClass::class)] 
    private EnergyClass $filtre_class;

     #[ORM\Column(type: Types::STRING, enumType: Type::class)] 
     private Type $type;
 

     #[ORM\OneToOne(targetEntity: Product::class, inversedBy: "simulation")]
     #[ORM\JoinColumn(name: "id_product", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
     private ?Product $product = null;
 
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getNoise(): ?float
    {
        return $this->noise;
    }

    public function setNoise(float $noise): static
    {
        $this->noise = $noise;

        return $this;
    }

    public function getPower(): ?float
    {
        return $this->power;
    }

    public function setPower(float $power): static
    {
        $this->power = $power;

        return $this;
    }

    public function getConsumptionLiter(): ?float
    {
        return $this->consumption_liter;
    }

    public function setConsumptionLiter(float $consumption_liter): static
    {
        $this->consumption_liter = $consumption_liter;

        return $this;
    }

    public function getConsumptionWatt(): ?float
    {
        return $this->consumption_watt;
    }

    public function setConsumptionWatt(float $consumption_watt): static
    {
        $this->consumption_watt = $consumption_watt;

        return $this;
    }

    public function getHdrConsumption(): ?float
    {
        return $this->hdr_consumption;
    }

    public function setHdrConsumption(float $hdr_consumption): static
    {
        $this->hdr_consumption = $hdr_consumption;

        return $this;
    }

    public function getSdrConsumption(): ?float
    {
        return $this->sdr_consumption;
    }

    public function setSdrConsumption(float $sdr_consumption): static
    {
        $this->sdr_consumption = $sdr_consumption;

        return $this;
    }

    public function getCapacity(): ?float
    {
        return $this->capacity;
    }

    public function setCapacity(float $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getDimension(): ?float
    {
        return $this->dimension;
    }

    public function setDimension(float $dimension): static
    {
        $this->dimension = $dimension;

        return $this;
    }

    public function getVolumeRefrigeration(): ?float
    {
        return $this->volume_refrigeration;
    }

    public function setVolumeRefrigeration(float $volume_refrigeration): static
    {
        $this->volume_refrigeration = $volume_refrigeration;

        return $this;
    }

    public function getVolumeFreezer(): ?float
    {
        return $this->volume_freezer;
    }

    public function setVolumeFreezer(float $volume_freezer): static
    {
        $this->volume_freezer = $volume_freezer;

        return $this;
    }

    public function getVolumeCollect(): ?float
    {
        return $this->volume_collect;
    }

    public function setVolumeCollect(float $volume_collect): static
    {
        $this->volume_collect = $volume_collect;

        return $this;
    }

    public function getSeer(): ?float
    {
        return $this->seer;
    }

    public function setSeer(float $seer): static
    {
        $this->seer = $seer;

        return $this;
    }

    public function getScop(): ?float
    {
        return $this->scop;
    }

    public function setScop(float $scop): static
    {
        $this->scop = $scop;

        return $this;
    }

    public function getCycleDuration(): ?\DateTimeInterface
    {
        return $this->cycle_duration;
    }

    public function setCycleDuration(\DateTimeInterface $cycle_duration): static
    {
        $this->cycle_duration = $cycle_duration;

        return $this;
    }

    public function getNbrCouvert(): ?int
    {
        return $this->nbr_couvert;
    }

    public function setNbrCouvert(int $nbr_couvert): static
    {
        $this->nbr_couvert = $nbr_couvert;

        return $this;
    }

    public function getNbBottle(): ?int
    {
        return $this->nb_bottle;
    }

    public function setNbBottle(int $nb_bottle): static
    {
        $this->nb_bottle = $nb_bottle;

        return $this;
    }

    public function getResolution(): ?float
    {
        return $this->resolution;
    }

    public function setResolution(float $resolution): static
    {
        $this->resolution = $resolution;

        return $this;
    }

    public function getDiagonal(): ?float
    {
        return $this->diagonal;
    }

    public function setDiagonal(float $diagonal): static
    {
        $this->diagonal = $diagonal;

        return $this;
    }
    public function getEnergyClass(): EnergyClass
    {
        return $this->energy_class;
    }

    public function setEnergyClass(EnergyClass $energy_class): self
    {
        $this->energy_class = $energy_class;
        return $this;
    }

    public function getCondensPerform(): EnergyClass
    {
        return $this->condens_perform;
    }

    public function setCondensPerform(EnergyClass $condens_perform): self
    {
        $this->condens_perform = $condens_perform;
        return $this;
    }
    
    public function getSpingdryClass(): EnergyClass
    {
        return $this->spindry_class;
    }

    public function setSpingdryClass(EnergyClass $spindry_class): self
    {
        $this->spindry_class = $spindry_class;
        return $this;
    }
    public function getSteamClass(): EnergyClass
    {
        return $this->steam_class;
    }

    public function setSteamClass(EnergyClass $steam_class): self
    {
        $this->steam_class = $steam_class;
        return $this;
    }
    public function getLightClass(): EnergyClass
    {
        return $this->light_class;
    }

    public function setLightClass(EnergyClass $light_class): self
    {
        $this->light_class = $light_class;
        return $this;
    }
    public function getFiltreClass(): EnergyClass
    {
        return $this->filtre_class;
    }

    public function setFiltreClass(EnergyClass $filtre_class): self
    {
        $this->filtre_class = $filtre_class;
        return $this;
    }
    public function getType(): Type
    {
        return $this->type;
    }

    public function setType(Type $type): self
    {
        $this->type = $type;
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
