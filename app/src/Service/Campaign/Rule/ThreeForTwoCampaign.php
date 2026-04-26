<?php

namespace App\Service\Campaign\Rule;

use App\Entity\Quote\Quote;
use App\Service\Campaign\CampaignInterface;

class ThreeForTwoCampaign implements CampaignInterface
{
    public function getName(): string
    {
        return '3 Al 2 Öde';
    }

    public function isEligible(Quote $quote): bool
    {
        $totalQty = 0;
        foreach ($quote->getItems() as $item) {
            $totalQty += $item->getQty();
        }

        return $totalQty >= 3;
    }

    public function apply(Quote $quote): float
    {
        $prices = [];
        foreach ($quote->getItems() as $item) {
            for ($i = 0; $i < $item->getQty(); $i++) {
                $prices[] = $item->getPrice();
            }
        }

        sort($prices);

        return $prices[0];
    }

    public function distributeDiscount(Quote $quote): void
    {
        $cheapestItem = null;
        $cheapestPrice = 0;

        foreach ($quote->getItems() as $item) {
            if ($cheapestPrice === 0 || $item->getPrice() < $cheapestPrice) {
                $cheapestPrice = $item->getPrice();
                $cheapestItem = $item;
            }
        }

        if ($cheapestItem !== null) {
            $cheapestItem->setDiscountAmount(round($cheapestPrice, 4));
        }
    }
}
