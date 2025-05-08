<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity:Client::class)]
    #[ORM\JoinColumn(name: 'id_client', referencedColumnName: 'id',nullable: true)]
    private ?Client $id_client =  null;

    #[ORM\Column(type: "datetime")]
    private $created_at;

    #[ORM\Column(type: "string", length: 20)]
    private $status = self::STATUS_PENDING;  

    const STATUS_PENDING = 'pending';
    const STATUS_VALIDATED = 'validated';
    const STATUS_CANCELLED = 'cancelled';
  
    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: CartContainer::class)]
    private Collection $cartContainers;
    
    public function getCartContainers(): Collection
    {
        return $this->cartContainers;
    }
    

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getIdClient(): ?Client
    {
        return $this->id_client;
    }

    public function setIdClient(?Client $id_client): self
    {
        $this->id_client = $id_client;

        return $this;
    }
    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

}
