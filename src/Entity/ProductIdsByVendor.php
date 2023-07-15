<?php

namespace App\Entity;

use App\Repository\ProductIdsByVendorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductIdsByVendorRepository::class)]
class ProductIdsByVendor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $itemVendorId = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $idArray = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemVendorId(): ?string
    {
        return $this->itemVendorId;
    }

    public function setItemVendorId(string $itemVendorId): self
    {
        $this->itemVendorId = $itemVendorId;

        return $this;
    }

    public function getIdArray(): array
    {
        return $this->idArray;
    }

    public function setIdArray(array $idArray): self
    {
        $this->idArray = $idArray;

        return $this;
    }
}
