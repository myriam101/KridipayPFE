<?php

namespace App\Entity;

use App\Repository\CatalogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CatalogRepository::class)]
class Catalog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_catalog = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $public = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdat = null;

    #[ORM\ManyToOne(targetEntity:Provider::class)]
   #[ORM\JoinColumn(name: 'id_provider', referencedColumnName: 'id_provider',nullable: true)]
   private ?Provider $id_provider =  null;

    public function getIdCatalog(): ?int
    {
        return $this->id_catalog;
    }

    public function getPublic(): ?int
    {
        return $this->public;
    }

    public function setPublic(int $public): static
    {
        $this->public = $public;

        return $this;
    }

    public function getCreatedat(): ?\DateTimeInterface
    {
        return $this->createdat;
    }

    public function setCreatedat(\DateTimeInterface $createdat): static
    {
        $this->createdat = $createdat;

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
