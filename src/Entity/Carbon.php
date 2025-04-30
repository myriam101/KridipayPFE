<?php

namespace App\Entity;

use App\Entity\Enum\Badge;
use App\Repository\CarbonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarbonRepository::class)]
class Carbon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_update = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_add = null;

    #[ORM\Column]
    private ?float $value = null;

    #[ORM\Column]
    private ?bool $visible = null;

    #[ORM\Column(type: Types::INTEGER, enumType: Badge::class)] 
    private Badge $badge;

    #[ORM\OneToOne(targetEntity: Product::class, inversedBy: "carbon")]
    #[ORM\JoinColumn(name: "id_product", referencedColumnName: "id", nullable: false)]
    private Product $product;
   
    
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateUpdate(): ?\DateTimeInterface
    {
        return $this->date_update;
    }

    public function setDateUpdate(\DateTimeInterface $date_update): static
    {
        $this->date_update = $date_update;

        return $this;
    }

    public function getDateAdd(): ?\DateTimeInterface
    {
        return $this->date_add;
    }

    public function setDateAdd(\DateTimeInterface $date_add): static
    {
        $this->date_add = $date_add;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): static
    {
        $this->value = $value;

        return $this;
    }
    
    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;

        return $this;
    }
    public function getBadge(): Badge
    {
        return $this->badge;
    }

    public function setBadge(Badge $badge): self
    {
        $this->badge = $badge;
        return $this;
    }
}
