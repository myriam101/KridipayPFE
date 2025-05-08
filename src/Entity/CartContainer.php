<?php

namespace App\Entity;

use App\Repository\CartContainerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartContainerRepository::class)]
class CartContainer
{
    const STATUS_PENDING = 'pending';
    const STATUS_VALIDATED = 'validated';
    const STATUS_CANCELLED = 'cancelled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

     // Many cart containers can contain the same product, but one product per cart container
     #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'cartContainers')]
     #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id')]
     private ?Product $product = null;
 
     // A cart container belongs to one cart
     #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'cartContainers')]
     #[ORM\JoinColumn(name: 'cart_id', referencedColumnName: 'id')]
     private ?Cart $cart = null;
 
     #[ORM\Column(type: "string", length: 20)]
     private $status = self::STATUS_PENDING;
 
    
     #[ORM\Column(type: 'integer')]
     private int $quantity = 1;
     
     public function getQuantity(): int
     {
         return $this->quantity;
     }
     
     public function setQuantity(int $quantity): self
     {
         $this->quantity = $quantity;
         return $this;
     }
     
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): self
    {
        $this->cart = $cart;
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
    



}
