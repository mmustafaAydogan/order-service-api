<?php

namespace App\Entity\Order;

use App\Enum\AddressType;
use App\Helper\Address;
use App\Repository\Order\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
#[ORM\Index(columns: ['customer_id', 'created_at'], name: 'idx_orders_customer_created')]
#[ORM\Cache(usage: 'READ_ONLY')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: OrderSequence::class, inversedBy: 'order')]
    #[ORM\JoinColumn(name: 'sequence_id', referencedColumnName: 'sequence_value', nullable: false)]
    private OrderSequence $sequence;

    #[ORM\Column(length: 10, unique: true)]
    #[Groups(['order:read'])]
    private string $orderNumber;

    #[ORM\Column]
    #[Groups(['order:read'])]
    private int $customerId;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4)]
    #[Groups(['order:read'])]
    private string $subtotal;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4)]
    #[Groups(['order:read'])]
    private string $discountAmount;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4)]
    #[Groups(['order:read'])]
    private string $shippingAmount;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4)]
    #[Groups(['order:read'])]
    private string $grandTotal;

    #[ORM\Column(nullable: true)]
    #[Groups(['order:read'])]
    private ?string $appliedCampaignName = null;

    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', cascade: ['persist'])]
    #[ORM\Cache(usage: 'READ_ONLY')]
    #[Groups(['order:read'])]
    private Collection $items;

    #[ORM\OneToMany(targetEntity: OrderAddress::class, mappedBy: 'order', cascade: ['persist'])]
    #[ORM\Cache(usage: 'READ_ONLY')]
    #[Groups(['order:read'])]
    private Collection $addresses;

    #[ORM\Column]
    #[Groups(['order:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): static
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function setCustomerId(int $customerId): static
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function getSubtotal(): float
    {
        return (float) $this->subtotal;
    }

    public function setSubtotal(float $subtotal): static
    {
        $this->subtotal = (string) $subtotal;

        return $this;
    }

    public function getDiscountAmount(): float
    {
        return (float) $this->discountAmount;
    }

    public function setDiscountAmount(float $discountAmount): static
    {
        $this->discountAmount = (string) $discountAmount;

        return $this;
    }

    public function getShippingAmount(): float
    {
        return (float) $this->shippingAmount;
    }

    public function setShippingAmount(float $shippingAmount): static
    {
        $this->shippingAmount = (string) $shippingAmount;

        return $this;
    }

    public function getGrandTotal(): float
    {
        return (float) $this->grandTotal;
    }

    public function setGrandTotal(float $grandTotal): static
    {
        $this->grandTotal = (string) $grandTotal;

        return $this;
    }

    public function getAppliedCampaignName(): ?string
    {
        return $this->appliedCampaignName;
    }

    public function setAppliedCampaignName(?string $appliedCampaignName): static
    {
        $this->appliedCampaignName = $appliedCampaignName;

        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): void
    {
        $this->items->add($item);
    }

    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addBillingAddress(Address $data): void
    {
        $this->addresses->add($this->buildAddress(AddressType::Billing, $data));
    }

    public function addShippingAddress(Address $data): void
    {
        $this->addresses->add($this->buildAddress(AddressType::Shipping, $data));
    }

    private function buildAddress(AddressType $type, Address $data): OrderAddress
    {
        $address = new OrderAddress();
        $address->setOrder($this);
        $address->setAddressType($type);
        $address->setFirstName($data->firstName);
        $address->setLastName($data->lastName);
        $address->setPhone($data->phone);
        $address->setAddressLine($data->addressLine);
        $address->setDistrict($data->district);
        $address->setCity($data->city);
        $address->setPostalCode($data->postalCode);
        $address->setCountryCode($data->countryCode);

        return $address;
    }

    public function getSequence(): OrderSequence
    {
        return $this->sequence;
    }

    public function setSequence(OrderSequence $sequence): static
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
