<?php

namespace App\Entity;

use App\Repository\SaleRepository;
use App\Entity\Customer;
use App\Entity\Inventory;
use App\Entity\Packages;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[ORM\Entity(repositoryClass: SaleRepository::class)]
class Sale
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $OrderType = 'REGULAR SALE';

    #[ORM\Column(type: 'integer')]
    private ?int $ExternalSalesOrderNumber;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $DeliveryDate = null;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private ?string $ShipViaCode = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $PaymentMethod = null;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private ?string $TaxCode = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $SaleAmount = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $TaxAmount = null;

    #[ORM\Column(type: 'float')]
    private ?float $TotalAmount = null;

    #[ORM\Column(type: 'float')]
    private ?float $DeliveryAmount = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'SalesWritten')]
    #[ORM\JoinColumn(nullable: false)]
    private ?\App\Entity\User $salesperson = null;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'sales_orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?\App\Entity\Customer $customer = null;

    #[ORM\Column(nullable: true)]
    private ?int $BcOrderNumber = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $BcStatus = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $PsStatus = 'Pending';

    private $subtotal;
    private $tax;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $SalePkgQuantities = null;

    #[ORM\ManyToMany(targetEntity: packages::class, inversedBy: 'PackageSales')]
    private Collection $SalePkgItems;

    #[ORM\OneToMany(mappedBy: 'sale', targetEntity: SaleLineItems::class, cascade: ["persist", "remove"])]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $SaleLineItems;

    #[ORM\Column]
    private ?float $TaxPercentage = null;

    public function __construct()
    {
        $this->SalePkgItems = new ArrayCollection();
        $this->SaleLineItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return strval($this->ExternalSalesOrderNumber);
    }


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

    public function getExternalSalesOrderNumber(): ?int
    {
        return $this->ExternalSalesOrderNumber;
    }

    public function setExternalSalesOrderNumber(int $ExternalSalesOrderNumber): self
    {
        if($ExternalSalesOrderNumber === null){
            $this->ExternalSalesOrderNumber = $this->getId() + 100000;

            return $this;
        }
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
        if($this->TaxAmount === null){
            $this->TaxAmount = ($this->getSaleAmount() + $this->getDeliveryAmount()) * $this->getTaxPercentage();

            return $this;
        };
        $this->TaxAmount = $TaxAmount;

        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->TotalAmount;
    }

    public function setTotalAmount(float $TotalAmount): self
    {

        if($this->TotalAmount === null){
            $this->TotalAmount = $this->getSaleAmount() + $this->getDeliveryAmount() + $this->getTaxAmount();

            return $this;
        };
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

    public function getBcOrderNumber(): ?int
    {
        return $this->BcOrderNumber;
    }

    public function setBcOrderNumber(?int $BcOrderNumber): self
    {
        $this->BcOrderNumber = $BcOrderNumber;

        return $this;
    }

    public function getBcStatus(): ?string
    {
        return $this->BcStatus;
    }

    public function setBcStatus(?string $BcStatus): self
    {
        $this->BcStatus = $BcStatus;

        return $this;
    }

    public function getPsStatus(): ?string
    {
        $PsStatus = $this->PsStatus;

        return $PsStatus;
    }

    public function setPsStatus(?string $PsStatus): self
    {
        $this->PsStatus = $PsStatus;

        return $this;
    }

    public function getSalePkgQuantities(): array
    {
        return $this->SalePkgQuantities;
    }

    public function setSalePkgQuantities(?array $SalePkgQuantities): self
    {
        $this->SalePkgQuantities = $SalePkgQuantities;

        return $this;
    }

    /**
     * @return Collection<int, Packages>
     */
    public function getSalePkgItems(): Collection
    {
        return $this->SalePkgItems;
    }

    public function addSalePkgItem(Packages $salePkgItem): self
    {
        if (!$this->SalePkgItems->contains($salePkgItem)) {
            $this->SalePkgItems->add($salePkgItem);
        }

        return $this;
    }

    public function removeSalePkgItem(Packages $salePkgItem): self
    {
        $this->SalePkgItems->removeElement($salePkgItem);

        return $this;
    }

    /**
     * @return Collection<int, SaleLineItems>
     */
    public function getSaleLineItems(): Collection
    {
        return $this->SaleLineItems;
    }

    public function addSaleLineItem(SaleLineItems $saleLineItem): self
    {
        if (!$this->SaleLineItems->contains($saleLineItem)) {
            $this->SaleLineItems->add($saleLineItem);
            $saleLineItem->setSale($this);
        }

        return $this;
    }

    public function removeSaleLineItem(SaleLineItems $saleLineItem): self
    {
        if ($this->SaleLineItems->removeElement($saleLineItem)) {
            // set the owning side to null (unless already changed)
            if ($saleLineItem->getSale() === $this) {
                $saleLineItem->setSale(null);
            }
        }

        return $this;
    }

    public function getTaxPercentage(): ?float
    {
        return $this->TaxPercentage;
    }

    public function setTaxPercentage(float $TaxPercentage): self
    {
        $this->TaxPercentage = $TaxPercentage;

        return $this;
    }
}
