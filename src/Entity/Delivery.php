<?php

namespace App\Entity;

use App\Repository\DeliveryRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: DeliveryRepository::class)]
class Delivery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $carbon_footprint = null;

    #[ORM\Column]
    private ?float $distance = null;

    #[ORM\Column]
    private ?bool $everyday_ride = null;

    #[ORM\Column(type: Types::STRING, enumType: Modeliv::class)] 
    private Modeliv $modeliv;

    #[ORM\Column(type: Types::STRING, enumType: ClientRide::class)] 
    private ClientRide $client_ride;

    #[ORM\ManyToOne(targetEntity:Provider::class)]
    #[ORM\JoinColumn(name: 'id_provider', referencedColumnName: 'id_provider',nullable: true)]
    private ?Provider $id_provider =  null;
 
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCarbonFootprint(): ?float
    {
        return $this->carbon_footprint;
    }

    public function setCarbonFootprint(float $carbon_footprint): static
    {
        $this->carbon_footprint = $carbon_footprint;

        return $this;
    }

    public function getDistance(): ?float
    {
        return $this->distance;
    }

    public function setDistance(float $distance): static
    {
        $this->distance = $distance;

        return $this;
    }

    public function isEverydayRide(): ?bool
    {
        return $this->everyday_ride;
    }

    public function setEverydayRide(bool $everyday_ride): static
    {
        $this->everyday_ride = $everyday_ride;

        return $this;
    }
    public function getModeliv(): Modeliv
    {
        return $this->modeliv;
    }

    public function setModeliv(Modeliv $modeliv): self
    {
        $this->modeliv = $modeliv;
        return $this;
    }
    public function getClientride(): ClientRide
    {
        return $this->client_ride;
    }

    public function setClientride(ClientRide $client_ride): self
    {
        $this->client_ride = $client_ride;
        return $this;
    }
    public function getIdProvider(): ?Provider
    {
        return $this->id_provider;
    }

    public function setIdProvider(?Provider $id_provider): self
    {
        $this->id_provider = $id_provider;

        return $this;
    }
}
