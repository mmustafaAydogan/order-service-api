<?php

namespace App\Entity\Order;

use App\Repository\Order\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: 'order_items')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order_item:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private Order $order;

    #[ORM\Column]
    #[Groups(['order_item:read'])]
    private int $productId;

    #[ORM\Column(length: 255)]
    #[Groups(['order_item:read'])]
    private string $productTitle;

    #[ORM\Column]
    #[Groups(['order_item:read'])]
    private int $qty;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4)]
    #[Groups(['order_item:read'])]
    private string $price;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4)]
    #[Groups(['order_item:read'])]
    private string $rowTotal;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4, options: ['default' => '0.0000'])]
    #[Groups(['order_item:read'])]
    private string $discountAmount = '0.0000';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): static
    {
        $this->productId = $productId;

        return $this;
    }

    public function getProductTitle(): string
    {
        return $this->productTitle;
    }

    public function setProductTitle(string $productTitle): static
    {
        $this->productTitle = $productTitle;

        return $this;
    }

    public function getQty(): int
    {
        return $this->qty;
    }

    public function setQty(int $qty): static
    {
        $this->qty = $qty;

        return $this;
    }

    public function getPrice(): float
    {
        return (float) $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = (string) $price;

        return $this;
    }

    public function getRowTotal(): float
    {
        return (float) $this->rowTotal;
    }

    public function setRowTotal(float $rowTotal): static
    {
        $this->rowTotal = (string) $rowTotal;

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
}
