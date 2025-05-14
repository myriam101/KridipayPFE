<?php

namespace App\Entity;

use App\Repository\CatalogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;


#[ORM\Entity(repositoryClass: CatalogRepository::class)]
class Catalog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_catalog = null;

    #[ORM\Column(type: "boolean")]
    private $public;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdat = null;

    #[ORM\ManyToOne(targetEntity:Provider::class)]
   #[ORM\JoinColumn(name: 'id_provider', referencedColumnName: 'id_provider',nullable: true)]
   private ?Provider $id_provider =  null;

   #[ORM\OneToMany(targetEntity: ProductCatalog::class, mappedBy: 'catalog')]
   private Collection $productCatalogs;

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
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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
    
    /**
     * @return Collection<int, ProductCatalog>
     */
    public function getProductCatalogs(): Collection
    {
        return $this->productCatalogs;
    }

    public function addProductCatalog(ProductCatalog $productCatalog): static
    {
        if (!$this->productCatalogs->contains($productCatalog)) {
            $this->productCatalogs->add($productCatalog);
            $productCatalog->setCatalog($this);
        }

        return $this;
    }

    public function removeProductCatalog(ProductCatalog $productCatalog): static
    {
        if ($this->productCatalogs->removeElement($productCatalog)) {
            // set the owning side to null (unless already changed)
            if ($productCatalog->getCatalog() === $this) {
                $productCatalog->setCatalog(null);
            }
        }

        return $this;
    }

}
