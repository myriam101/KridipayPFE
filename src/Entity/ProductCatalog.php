<?php

namespace App\Entity;

use App\Repository\ProductcatalogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductcatalogRepository::class)]
class ProductCatalog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productCatalogs')]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'productCatalogs')]
    #[ORM\JoinColumn(name: 'id_catalog', referencedColumnName: 'id_catalog',nullable: true)]
    private ?Catalog $catalog = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getCatalog(): ?Catalog
    {
        return $this->catalog;
    }

    public function setCatalog(?Catalog $catalog): static
    {
        $this->catalog = $catalog;

        return $this;
    }
}
