<?php

namespace App\Entity;

use App\Repository\BonifPointRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Enum\Typepoint;

#[ORM\Entity(repositoryClass: BonifPointRepository::class)]
class BonifPoint
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_win = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_use = null;

    #[ORM\Column]
    private ?int $nbr_pt = null;

    #[ORM\ManyToOne(targetEntity:Client::class)]
    #[ORM\JoinColumn(name: 'id_client', referencedColumnName: 'id',nullable: true)]
    private ?Client $id_client =  null;
 
    #[ORM\Column(type: Types::STRING, enumType: Typepoint::class)] 
    private Typepoint $type_point;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateWin(): ?\DateTimeInterface
    {
        return $this->date_win;
    }

    public function setDateWin(\DateTimeInterface $date_win): static
    {
        $this->date_win = $date_win;

        return $this;
    }

    public function getDateUse(): ?\DateTimeInterface
    {
        return $this->date_use;
    }

    public function setDateUse(\DateTimeInterface $date_use): static
    {
        $this->date_use = $date_use;

        return $this;
    }

    public function getNbrPt(): ?int
    {
        return $this->nbr_pt;
    }

    public function setNbrPt(int $nbr_pt): static
    {
        $this->nbr_pt = $nbr_pt;

        return $this;
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
    public function getTypePoint(): Typepoint
    {
        return $this->type_point;
    }

    public function setTypePoint(Typepoint $type_point): self
    {
        $this->type_point = $type_point;
        return $this;
    }
}
