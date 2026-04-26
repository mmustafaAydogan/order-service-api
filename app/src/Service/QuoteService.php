<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\Quote\Quote;
use App\Entity\Quote\QuoteItem;
use App\Exception\InsufficientStockException;
use App\Exception\ProductNotFoundException;
use App\Exception\QuoteNotFoundException;
use App\Repository\ProductRepository;
use App\Repository\Quote\QuoteRepository;
use App\Service\Campaign\CampaignEngine;
use Doctrine\ORM\EntityManagerInterface;

class QuoteService
{
    private const float SHIPPING_COST = 10.0;
    private const float FREE_SHIPPING_THRESHOLD = 50.0;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly QuoteRepository $quoteRepository,
        private readonly CampaignEngine $campaignEngine,
        private readonly ProductRepository $productRepository,
    ) {}

    public function getOrCreateQuote(int $customerId): Quote
    {
        $quote = $this->quoteRepository->findOneBy(['customerId' => $customerId]);

        if (!$quote) {
            $quote = new Quote();
            $quote->setCustomerId($customerId);
            $this->em->persist($quote);
            $this->em->flush();
        }

        return $quote;
    }

    public function addItems(int $customerId, array $items): Quote
    {
        $aggregatedProducts = [];
        foreach ($items as $item) {
            $pId = $item['product_id'];
            $aggregatedProducts[$pId] = ($aggregatedProducts[$pId] ?? 0) + $item['qty'];
        }

        $products = $this->productRepository->findBy(['id' => array_keys($aggregatedProducts)]);

        $productMap = [];
        foreach ($products as $product) {
            $productMap[$product->getId()] = $product;
        }

        foreach (array_keys($aggregatedProducts) as $productId) {
            if (!isset($productMap[$productId])) {
                throw new ProductNotFoundException(sprintf('Product with id %d not found.', $productId));
            }
        }

        $quote = $this->getOrCreateQuote($customerId);

        foreach ($aggregatedProducts as $productId => $qty) {
            $product = $productMap[$productId];
            $existingItem = $this->findItem($quote, $product);
            $totalQty = $qty + ($existingItem ? $existingItem->getQty() : 0);

            if ($totalQty > $product->getStockQuantity()) {
                throw new InsufficientStockException(
                    sprintf('Insufficient stock for "%s". Available Stock: %d.', $product->getTitle(), $product->getStockQuantity())
                );
            }
        }

        foreach ($aggregatedProducts as $productId => $qty) {
            $product = $productMap[$productId];
            $existingItem = $this->findItem($quote, $product);
            $totalQty = $qty + ($existingItem ? $existingItem->getQty() : 0);

            if ($existingItem) {
                $existingItem->setQty($totalQty);
                $existingItem->setRowTotal($existingItem->getPrice() * $totalQty);
            } else {
                $item = new QuoteItem();
                $item->setQuote($quote);
                $item->setProduct($product);
                $item->setPrice($product->getListPrice());
                $item->setQty($qty);
                $item->setRowTotal($product->getListPrice() * $qty);
                $quote->addItem($item);
                $this->em->persist($item);
            }
        }

        $this->recalculate($quote);
        $this->em->flush();

        return $quote;
    }

    public function removeItems(int $customerId, array $itemsToRemove): ?Quote
    {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->findOneBy(['customerId' => $customerId]);

        if (!$quote) {
            throw new QuoteNotFoundException('No active quote found.');
        }

        $productIds = array_column($itemsToRemove, 'product_id');
        $products = $this->productRepository->findBy(['id' => $productIds]);
        $productMap = [];
        foreach ($products as $product) {
            $productMap[$product->getId()] = $product;
        }

        foreach ($itemsToRemove as $itemData) {
            $product = $productMap[$itemData['product_id']] ?? null;

            if (!$product) {
                throw new ProductNotFoundException(
                    sprintf('Product with id %d not found.', $itemData['product_id'])
                );
            }

            $item = $this->findItem($quote, $product);

            if (!$item) {
                continue;
            }

            if (isset($itemData['qty'])) {
                $newQty = $item->getQty() - $itemData['qty'];

                if ($newQty <= 0) {
                    $this->em->remove($item);
                    $quote->getItems()->removeElement($item);
                } else {
                    $item->setQty($newQty);
                    $item->setRowTotal($item->getPrice() * $newQty);
                }
            } else {
                $this->em->remove($item);
                $quote->getItems()->removeElement($item);
            }
        }

        if ($quote->getItems()->isEmpty()) {
            $this->em->remove($quote);
            $this->em->flush();

            return null;
        }

        $this->recalculate($quote);
        $this->em->flush();

        return $quote;
    }


    public function getQuote(int $customerId): Quote
    {
        $quote = $this->quoteRepository->findOneBy(['customerId' => $customerId]);

        if (!$quote) {
            throw new QuoteNotFoundException('No active quote found.');
        }

        return $quote;
    }

    private function recalculate(Quote $quote): void
    {
        $subtotal = 0;
        /** @var QuoteItem $item */
        foreach ($quote->getItems() as $item) {
            $subtotal += $item->getRowTotal();
        }
        $quote->setSubtotal(round($subtotal, 4));

        $campaignResult = $this->campaignEngine->evaluate($quote);
        $quote->setDiscountAmount($campaignResult->discountAmount);
        $quote->setAppliedCampaignName($campaignResult->appliedCampaignName);

        $discountedTotal = $subtotal - $campaignResult->discountAmount;
        $shipping = $discountedTotal >= self::FREE_SHIPPING_THRESHOLD ? 0 : self::SHIPPING_COST;
        $quote->setShippingAmount($shipping);

        $quote->setGrandTotal(round($discountedTotal + $shipping, 4));
    }

    private function findItem(Quote $quote, Product $product): ?QuoteItem
    {
        foreach ($quote->getItems() as $item) {
            if ($item->getProduct()->getId() === $product->getId()) {
                return $item;
            }
        }

        return null;
    }
}
