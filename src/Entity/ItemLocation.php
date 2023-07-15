<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ItemLocationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemLocationRepository::class)]
class ItemLocation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $itemId = null;

    #[ORM\Column(type: 'integer')]
    private ?int $quantity = null;

    #[ORM\Column(type: 'string', length: 1)]
    private ?string $status = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $location = null;

    #[ORM\ManyToOne(targetEntity: Inventory::class, inversedBy: 'stock')]
    private ?\App\Entity\Inventory $locItemId = null;

    public function __toString(): string
    {
        return $this->location;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemId(): ?string
    {
        return $this->itemId;
    }

    public function setItemId(string $itemId): self
    {
        $this->itemId = $itemId;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getLocItemId(): ?Inventory
    {
        return $this->locItemId;
    }

    public function setLocItemId(?Inventory $locItemId): self
    {
        $this->locItemId = $locItemId;

        return $this;
    }
}