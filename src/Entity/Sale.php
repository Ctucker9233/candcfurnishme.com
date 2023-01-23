<?php

namespace App\Entity;

use App\Repository\SaleRepository;
use App\Entity\Customer;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

/**
 * @ORM\Entity(repositoryClass=SaleRepository::class)
 */
class Sale
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $OrderType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ExternalSalesOrderNumber;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $DeliveryDate;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    private $ShipViaCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $PaymentMethod;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     */
    private $TaxCode;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $SaleAmount;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $TaxAmount;

    /**
     * @ORM\Column(type="float")
     */
    private $TotalAmount;

    /**
     * @ORM\Column(type="float")
     */
    private $DeliveryAmount;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="SalesWritten")
     * @ORM\JoinColumn(nullable=false)
     */
    private $salesperson;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="sales_orders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\ManyToMany(targetEntity=Inventory::class, mappedBy="salesOrder")
     */
    private $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getOrderType(): ?string
    {
        return $this->OrderType;
    }

    public function setOrderType(string $OrderType): self
    {
        $this->OrderType = $OrderType;

        return $this;
    }

    public function getExternalSalesOrderNumber(): ?string
    {
        return $this->ExternalSalesOrderNumber;
    }

    public function setExternalSalesOrderNumber(string $ExternalSalesOrderNumber): self
    {
        $this->ExternalSalesOrderNumber = $ExternalSalesOrderNumber;

        return $this;
    }

    public function getDeliveryDate(): ?\DateTimeInterface
    {
        return $this->DeliveryDate;
    }

    public function setDeliveryDate(?\DateTimeInterface $DeliveryDate): self
    {
        $this->DeliveryDate = $DeliveryDate;

        return $this;
    }

    public function getShipViaCode(): ?string
    {
        return $this->ShipViaCode;
    }

    public function setShipViaCode(?string $ShipViaCode): self
    {
        $this->ShipViaCode = $ShipViaCode;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->PaymentMethod;
    }

    public function setPaymentMethod(?string $PaymentMethod): self
    {
        $this->PaymentMethod = $PaymentMethod;

        return $this;
    }

    public function getTaxCode(): ?string
    {
        return $this->TaxCode;
    }

    public function setTaxCode(?string $TaxCode): self
    {
        $this->TaxCode = $TaxCode;

        return $this;
    }

    public function getSaleAmount(): ?float
    {
        return $this->SaleAmount;
    }

    public function setSaleAmount(?float $SaleAmount): self
    {
        $this->SaleAmount = $SaleAmount;

        return $this;
    }

    public function getTaxAmount(): ?float
    {
        return $this->TaxAmount;
    }

    public function setTaxAmount(?float $TaxAmount): self
    {
        $this->TaxAmount = $TaxAmount;

        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->TotalAmount;
    }

    public function setTotalAmount(float $TotalAmount): self
    {
        $this->TotalAmount = $TotalAmount;

        return $this;
    }

    public function getDeliveryAmount(): ?float
    {
        return $this->DeliveryAmount;
    }

    public function setDeliveryAmount(float $DeliveryAmount): self
    {
        $this->DeliveryAmount = $DeliveryAmount;

        return $this;
    }

    public function getSalesperson(): ?User
    {
        return $this->salesperson;
    }

    public function setSalesperson(?User $salesperson): self
    {
        $this->salesperson = $salesperson;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Collection<int, Inventory>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Inventory $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->addSalesOrder($this);
        }

        return $this;
    }

    public function removeItem(Inventory $item): self
    {
        if ($this->items->removeElement($item)) {
            $item->removeSalesOrder($this);
        }

        return $this;
    }
}
