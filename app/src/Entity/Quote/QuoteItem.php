<?php

namespace App\Entity\Quote;

use App\Entity\Product;
use App\Repository\Quote\QuoteItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: QuoteItemRepository::class)]
#[ORM\Table(name: 'quote_items')]
class QuoteItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['quote_item:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Quote::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private Quote $quote;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['quote_item:read'])]
    private Product $product;

    #[ORM\Column]
    #[Groups(['quote_item:read'])]
    private int $qty;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4)]
    #[Groups(['quote_item:read'])]
    private string $price;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4)]
    #[Groups(['quote_item:read'])]
    private string $rowTotal;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 4, options: ['default' => '0.0000'])]
    #[Groups(['quote_item:read'])]
    private string $discountAmount = '0.0000';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuote(): Quote
    {
        return $this->quote;
    }

    public function setQuote(Quote $quote): static
    {
        $this->quote = $quote;

        return $this;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): static
    {
        $this->product = $product;

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
