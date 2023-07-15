<?php

namespace App\Entity;

use App\Entity\Inventory;
use App\Entity\Sale;
use App\Repository\SaleLineItemsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SaleLineItemsRepository::class)]
class SaleLineItems
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\ManyToOne(targetEntity: Inventory::class, inversedBy: 'SaleLineItems')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Inventory $item = null;

    #[ORM\ManyToOne(inversedBy: 'SaleLineItems', cascade: ["persist", "remove"]) ]
    private ?Sale $sale = null;

    public function __toString(){
        return $this->quantity ."x " . $this->getItem()->getItemDescription();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getItem(): ?Inventory
    {
        return $this->item;
    }

    public function setItem(?Inventory $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getSale(): ?Sale
    {
        return $this->sale;
    }

    public function setSale(?Sale $sale): self
    {
        $this->sale = $sale;

        return $this;
    }
}
