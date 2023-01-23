<?php

namespace App\Entity;

use App\Entity\itemLocation;
use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InventoryRepository::class)
 */
class Inventory
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
    private $itemID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $itemDescription;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mfcsku;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pictureLink;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $upcCode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $vendorId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $vendorName;

    /**
     * @ORM\Column(type="boolean")
     */
    private $webHide;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $webUrl;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $backorderCode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $category;

    /**
     * @ORM\ManyToMany(targetEntity=sale::class, inversedBy="items")
     */
    private $salesOrder;

    /**
     * @ORM\OneToMany(targetEntity=itemLocation::class, mappedBy="locItemId")
     */
    private $stock;

    public function __construct()
    {
        $this->salesOrder = new ArrayCollection();
        $this->stock = new ArrayCollection();
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
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
     * @return Collection<int, sale>
     */
    public function getSalesOrder(): Collection
    {
        return $this->salesOrder;
    }

    public function addSalesOrder(sale $salesOrder): self
    {
        if (!$this->salesOrder->contains($salesOrder)) {
            $this->salesOrder[] = $salesOrder;
        }

        return $this;
    }

    public function removeSalesOrder(sale $salesOrder): self
    {
        $this->salesOrder->removeElement($salesOrder);

        return $this;
    }

    /**
     * @return Collection<int, itemLocation>
     */
    public function getStock(): Collection
    {
        return $this->stock;
    }

    public function addStock(itemLocation $stock): self
    {
        if (!$this->stock->contains($stock)) {
            $this->stock[] = $stock;
            $stock->setLocItemId($this);
        }

        return $this;
    }

    public function removeStock(itemLocation $stock): self
    {
        if ($this->stock->removeElement($stock)) {
            // set the owning side to null (unless already changed)
            if ($stock->getLocItemId() === $this) {
                $stock->setLocItemId(null);
            }
        }

        return $this;
    }
}
