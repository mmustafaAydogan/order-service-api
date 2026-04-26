<?php

namespace App\Entity\Quote;

use App\Repository\Quote\QuoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: QuoteRepository::class)]
#[ORM\Table(name: 'quotes')]
#[ORM\Index(columns: ['customer_id'], name: 'idx_quotes_customer_id')]
#[ORM\HasLifecycleCallbacks]
class Quote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['quote:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['quote:read'])]
    private int $customerId;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4, options: ['default' => '0.0000'])]
    #[Groups(['quote:read'])]
    private string $subtotal = '0.0000';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4, options: ['default' => '0.0000'])]
    #[Groups(['quote:read'])]
    private string $discountAmount = '0.0000';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4, options: ['default' => '0.0000'])]
    #[Groups(['quote:read'])]
    private string $shippingAmount = '0.0000';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4, options: ['default' => '0.0000'])]
    #[Groups(['quote:read'])]
    private string $grandTotal = '0.0000';

    #[ORM\Column(nullable: true)]
    #[Groups(['quote:read'])]
    private ?string $appliedCampaignName = null;

    #[ORM\OneToMany(targetEntity: QuoteItem::class, mappedBy: 'quote', cascade: ['persist', 'remove'])]
    #[Groups(['quote:read'])]
    private Collection $items;

    #[ORM\Column]
    #[Groups(['quote:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Groups(['quote:read'])]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function addItem(QuoteItem $item): void
    {
        $this->items->add($item);
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
