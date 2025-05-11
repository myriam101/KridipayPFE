<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['client:read'])]
    private ?string $adress = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['client:read'])]
    private ?int $totalBonifPts = 0;

    #[ORM\Column(nullable: true)]
    #[Groups(['client:read'])]
    private ?int $score_carbone = 0;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Cart::class)]
    private Collection $carts;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "clients")]
    #[Groups(['client:read'])]
    private $User;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): static
    {
        $this->adress = $adress;

        return $this;
    }
    public function getTotalBonifPts(): ?string
    {
        return $this->totalBonifPts;
    }

    public function setTotalBonifPts(string $totalBonifPts): static
    {
        $this->totalBonifPts = $totalBonifPts;

        return $this;
    }
    public function getScorecarbone(): ?string
    {
        return $this->score_carbone;
    }

    public function setScorecarbone(string $score_carbone): static
    {
        $this->score_carbone = $score_carbone;

        return $this;
    }
    
    /**
     * @return Collection<int, Cart>
     */
    public function getCarts(): Collection
    {
        return $this->carts;
    }

    public function addCart(Cart $cart): self
    {
        if (!$this->carts->contains($cart)) {
            $this->carts[] = $cart;
            $cart->setIdClient($this);
        }

        return $this;
    }

    public function removeCart(Cart $cart): self
    {
        if ($this->carts->removeElement($cart)) {
            // set the owning side to null (unless already changed)
            if ($cart->getIdClient() === $this) {
                $cart->setIdClient(null);
            }
        }

        return $this;
    }
    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

}
