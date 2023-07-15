<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use App\Entity\Sale;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $address1 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $address2 = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $city = null;

    #[ORM\Column(type: 'string', length: 2)]
    private ?string $state = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $postalcode = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $phone1 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $phone2 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $phone3 = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $customerid = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isDeleted = null;

    #[ORM\OneToMany(targetEntity: Sale::class, mappedBy: 'customer')]
    private Collection $salesOrders;

    #[ORM\Column(nullable: true)]
    private ?int $BcCustId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shippingAddress1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shippingAddress2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shippingCity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shippingState = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shippingPostalcode = null;

    public function __construct()
    {
        $this->salesOrders = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function setAddress1(string $address1): self
    {
        $this->address1 = $address1;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): self
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address1.', '.$this->address2;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getPostalcode(): ?string
    {
        return $this->postalcode;
    }

    public function setPostalcode(string $postalcode): self
    {
        $this->postalcode = $postalcode;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone1(): ?string
    {
        return $this->phone1;
    }

    public function setPhone1(string $phone1): self
    {
        $this->phone1 = $phone1;

        return $this;
    }

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function setPhone2(?string $phone2): self
    {
        $this->phone2 = $phone2;

        return $this;
    }

    public function getPhone3(): ?string
    {
        return $this->phone3;
    }

    public function setPhone3(?string $phone3): self
    {
        $this->phone3 = $phone3;

        return $this;
    }

    public function getCustomerid(): ?string
    {
        return $this->customerid;
    }

    public function setCustomerid(?string $customerid): self
    {
        $this->customerid = $customerid;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * @return Collection<int, Sale>
     */
    public function getSalesOrders(): Collection
    {
        return $this->salesOrders;
    }

    public function addSalesOrder(Sale $salesOrder): self
    {
        if (!$this->salesOrders->contains($salesOrder)) {
            $this->salesOrders->add($salesOrder);
            $salesOrder->setCustomer($this);
        }

        return $this;
    }

    public function removeSalesOrder(Sale $salesOrder): self
    {
        if ($this->salesOrders->removeElement($salesOrder)) {
            // set the owning side to null (unless already changed)
            if ($salesOrder->getCustomer() === $this) {
                $salesOrder->setCustomer(null);
            }
        }

        return $this;
    }

    public function getBcCustId(): ?int
    {
        return $this->BcCustId;
    }

    public function setBcCustId(?int $BcCustId): self
    {
        $this->BcCustId = $BcCustId;

        return $this;
    }

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function getShippingAddress1(): ?string
    {
        return $this->shippingAddress1;
    }

    public function setShippingAddress1(?string $shippingAddress1): self
    {
        $this->shippingAddress1 = $shippingAddress1;

        return $this;
    }

    public function getShippingAddress2(): ?string
    {
        return $this->shippingAddress2;
    }

    public function setShippingAddress2(?string $shippingAddress2): self
    {
        $this->shippingAddress2 = $shippingAddress2;

        return $this;
    }

    public function getShippingCity(): ?string
    {
        return $this->shippingCity;
    }

    public function setShippingCity(?string $shippingCity): self
    {
        $this->shippingCity = $shippingCity;

        return $this;
    }

    public function getShippingState(): ?string
    {
        return $this->shippingState;
    }

    public function setShippingState(?string $shippingState): self
    {
        $this->shippingState = $shippingState;

        return $this;
    }

    public function getShippingPostalcode(): ?string
    {
        return $this->shippingPostalcode;
    }

    public function setShippingPostalcode(?string $shippingPostalcode): self
    {
        $this->shippingPostalcode = $shippingPostalcode;

        return $this;
    }
}
