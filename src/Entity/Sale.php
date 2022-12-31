<?php

namespace App\Entity;

use App\Repository\SaleRepository;
use App\Entity\Customer;
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
     * @ORM\ManyToOne(targetEntity=customer::class, inversedBy="RelatedSales")
     * @ORM\JoinColumn(nullable=false)
     */
    private $relationship;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRelationship(): ?customer
    {
        return $this->relationship;
    }

    public function setRelationship(?customer $relationship): self
    {
        $this->relationship = $relationship;

        return $this;
    }
}
