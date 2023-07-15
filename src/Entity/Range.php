<?php

namespace App\Entity;

use App\Repository\RangeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RangeRepository::class)]
#[ORM\Table(name: '`range`')]
class Range
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $minId = null;

    #[ORM\Column(length: 255)]
    private ?string $maxId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMinId(): ?string
    {
        return $this->minId;
    }

    public function setMinId(string $minId): self
    {
        $this->minId = $minId;

        return $this;
    }

    public function getMaxId(): ?string
    {
        return $this->maxId;
    }

    public function setMaxId(string $maxId): self
    {
        $this->maxId = $maxId;

        return $this;
    }
}
