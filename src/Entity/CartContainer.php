<?php

namespace App\Entity;

use App\Repository\CartContainerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartContainerRepository::class)]
class CartContainer
{
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
}
