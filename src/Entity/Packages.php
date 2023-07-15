<?php

namespace App\Entity;

use App\Repository\PackagesRepository;
use App\Entity\Inventory;
use App\Entity\Sale;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PackagesRepository::class)]
class Packages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(mappedBy: 'Package', targetEntity: Inventory::class, cascade: ['persist'])]
    private Collection $itemIds;

    #[ORM\Column(length: 255)]
    private ?string $Description = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $ComponentIds = [];

    #[ORM\Column]
    private ?int $PkgQuantity = null;

    #[ORM\Column(length: 255)]
    private ?string $packageId = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $componentQuantity = [];

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $BcPackDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $PackPicture = null;

    #[ORM\Column(nullable: true)]
    private ?int $BcPackId = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\ManyToMany(targetEntity: Sale::class, mappedBy: 'SalePkgItems')]
    private Collection $PackageSales;

    public function __construct()
    {
        $this->itemIds = new ArrayCollection();
        $this->PackageSales = new ArrayCollection();
    }

    public function __toString(){
        return $this->Description . " $" . $this->price/100;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }

    public function getComponentIds(): array
    {
        return $this->ComponentIds;
    }

    public function setComponentIds(array $ComponentIds): self
    {
        $this->ComponentIds = $ComponentIds;

        return $this;
    }

    public function getPkgQuantity(): ?int
    {
        return $this->PkgQuantity;
    }

    public function setPkgQuantity(int $PkgQuantity): self
    {
        $this->PkgQuantity = $PkgQuantity;

        return $this;
    }

    public function getPackageId(): ?string
    {
        return $this->packageId;
    }

    public function setPackageId(string $packageId): self
    {
        $this->packageId = $packageId;

        return $this;
    }

    public function getComponentQuantity(): array
    {
        return $this->componentQuantity;
    }

    public function setComponentQuantity(?array $componentQuantity): self
    {
        $this->componentQuantity = $componentQuantity;

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

    public function getBcPackDescription(): ?string
    {
        return $this->BcPackDescription;
    }

    public function setBcPackDescription(?string $BcPackDescription): self
    {
        $this->BcPackDescription = $BcPackDescription;

        return $this;
    }

    public function getPackPicture(): ?string
    {
        return $this->PackPicture;
    }

    public function setPackPicture(?string $PackPicture): self
    {
        $this->PackPicture = $PackPicture;

        return $this;
    }

    public function getBcPackId(): ?int
    {
        return $this->BcPackId;
    }

    public function setBcPackId(?int $BcPackId): self
    {
        $this->BcPackId = $BcPackId;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, Inventory>
     */
    public function getItemIds(): Collection
    {
        return $this->itemIds;
    }

    public function addItemId(Inventory $itemId): self
    {
        if (!$this->itemIds->contains($itemId)) {
            $this->itemIds->add($itemId);
            $itemId->addPackage($this);
        }

        return $this;
    }

    public function removeItemId(Inventory $itemId): self
    {
        if ($this->itemIds->removeElement($itemId)) {
            $itemId->removePackage($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Sale>
     */
    public function getPackageSales(): Collection
    {
        return $this->PackageSales;
    }

    public function addPackageSale(Sale $packageSale): self
    {
        if (!$this->PackageSales->contains($packageSale)) {
            $this->PackageSales->add($packageSale);
            $packageSale->addSalePkgItem($this);
        }

        return $this;
    }

    public function removePackageSale(Sale $packageSale): self
    {
        if ($this->PackageSales->removeElement($packageSale)) {
            $packageSale->removeSalePkgItem($this);
        }

        return $this;
    }
}
