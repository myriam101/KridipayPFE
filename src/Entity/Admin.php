<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
#[ORM\Table(name: '`admin`')]
class Admin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

     #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "clients")]
    #[Groups(['client:read'])]
    private $User;


    public function getId(): ?int
    {
        return $this->id;
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
