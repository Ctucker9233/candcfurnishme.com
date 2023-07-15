<?php

namespace App\Entity;

use App\Entity\ItemLocation;
use App\Entity\Packages;
use App\Entity\Sale;
use App\Entity\SaleLineItems;
use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
class Inventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $itemID = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $itemDescription = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $mfcsku = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $pictureLink = null;

    #[ORM\Column(type: 'integer')]
    private ?int $price = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $upcCode = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $vendorId = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $vendorName = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $webHide = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $webUrl = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $backorderCode = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $category = null;

    #[ORM\OneToMany(targetEntity: ItemLocation::class, mappedBy: 'locItemId')]
    private Collection $stock;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?bool $isDeleted = null;

    #[ORM\Column]
    private ?bool $isPackage = null;

    #[ORM\ManyToMany(targetEntity: Packages::class, inversedBy: 'itemIds', cascade: ['persist'])]
    private Collection $Package;

    #[ORM\Column(nullable: true)]
    private ?int $BCItemId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $BcItemDescription = null;

    #[ORM\Column(nullable: true)]
    private ?int $BCVendorId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gtin = null;

    #[ORM\OneToMany(targetEntity: SaleLineItems::class, mappedBy: 'item', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $SaleLineItems;

    #[ORM\Column]
    private ?bool $discontinued = null;

    public function __construct()
    {
        $this->stock = new ArrayCollection();
        $this->Package = new ArrayCollection();
        $this->SaleLineItems = new ArrayCollection();
    }

    public function __toString(){
        return $this->quantity . "x " . $this->itemDescription . " $" . $this->price/100;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItemID(): ?string
    {
        return $this->itemID;
    }

    public function setItemID(string $itemID): self
    {
        $this->itemID = $itemID;

        return $this;
    }

    public function getItemDescription(): ?string
    {
        return $this->itemDescription;
    }

    public function setItemDescription(string $itemDescription): self
    {
        $this->itemDescription = $itemDescription;

        return $this;
    }

    public function getMfcsku(): ?string
    {
        return $this->mfcsku;
    }

    public function setMfcsku(string $mfcsku): self
    {
        $this->mfcsku = $mfcsku;

        return $this;
    }

    public function getPictureLink(): ?string
    {
        return $this->pictureLink;
    }

    public function setPictureLink(?string $pictureLink): self
    {
        $this->pictureLink = $pictureLink;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getUpcCode(): ?string
    {
        return $this->upcCode;
    }

    public function setUpcCode(string $upcCode): self
    {
        $this->upcCode = $upcCode;

        return $this;
    }

    public function getVendorId(): ?string
    {
        return $this->vendorId;
    }

    public function setVendorId(string $vendorId): self
    {
        $this->vendorId = $vendorId;

        return $this;
    }

    public function getVendorName(): ?string
    {
        return $this->vendorName;
    }

    public function setVendorName(string $vendorName): self
    {
        $this->vendorName = $vendorName;

        return $this;
    }

    public function isWebHide(): ?bool
    {
        return $this->webHide;
    }

    public function setWebHide(bool $webHide): self
    {
        $this->webHide = $webHide;

        return $this;
    }

    public function getWebUrl(): ?string
    {
        return $this->webUrl;
    }

    public function setWebUrl(?string $webUrl): self
    {
        $this->webUrl = $webUrl;

        return $this;
    }

    public function getBackorderCode(): ?string
    {
        return $this->backorderCode;
    }

    public function setBackorderCode(string $backorderCode): self
    {
        $this->backorderCode = $backorderCode;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, ItemLocation>
     */
    public function getStock(): Collection
    {
        return $this->stock;
    }

    public function addStock(ItemLocation $stock): self
    {
        if (!$this->stock->contains($stock)) {
            $this->stock->add($stock);
            $stock->setLocItemId($this);
        }

        return $this;
    }

    public function removeStock(ItemLocation $stock): self
    {
        if ($this->stock->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getLocItemId() === $this) {
                $stock->setLocItemId(null);
            }
        }

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

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function isIsPackage(): ?bool
    {
        return $this->isPackage;
    }

    public function setIsPackage(bool $isPackage): self
    {
        $this->isPackage = $isPackage;

        return $this;
    }

    public function getBCItemId(): ?int
    {
        return $this->BCItemId;
    }

    public function setBCItemId(?int $BCItemId): self
    {
        $this->BCItemId = $BCItemId;

        return $this;
    }

    public function getBcItemDescription(): ?string
    {
        return $this->BcItemDescription;
    }

    public function setBcItemDescription(?string $BcItemDescription): self
    {
        $this->BcItemDescription = $BcItemDescription;

        return $this;
    }

    public function getBCVendorId(): ?int
    {
        return $this->BCVendorId;
    }

    public function setBCVendorId(?int $BCVendorId): self
    {
        $this->BCVendorId = $BCVendorId;

        return $this;
    }

    public function getGtin(): ?string
    {
        return $this->gtin;
    }

    public function setGtin(?string $gtin): self
    {
        $this->gtin = $gtin;

        return $this;
    }

    public function addPackage(Packages $package): self
    {
        if (!$this->Package->contains($package)) {
            $this->Package->add($package);
        }

        return $this;
    }

    public function removePackage(Packages $package): self
    {
        $this->Package->removeElement($package);

        return $this;
    }

    /**
     * @return Collection<int, Packages>
     */
    public function getPackage(): Collection
    {
        return $this->Package;
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
            $saleLineItem->setItem($this);
        }

        return $this;
    }

    public function removeSaleLineItem(SaleLineItems $saleLineItem): self
    {
        if ($this->SaleLineItems->removeElement($saleLineItem)) {
            // set the owning side to null (unless already changed)
            if ($saleLineItem->getItem() === $this) {
                $saleLineItem->setItem(null);
            }
        }

        return $this;
    }

    public function isDiscontinued(): ?bool
    {
        return $this->discontinued;
    }

    public function setDiscontinued(bool $discontinued): self
    {
        $this->discontinued = $discontinued;

        return $this;
    }
}
