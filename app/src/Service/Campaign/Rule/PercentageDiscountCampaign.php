<?php

namespace App\Service\Campaign\Rule;

use App\Entity\Quote\Quote;
use App\Service\Campaign\CampaignInterface;

class PercentageDiscountCampaign implements CampaignInterface
{
    private const MINIMUM_AMOUNT = 20.0; // 20 TL üzeri
    private const DISCOUNT_RATE = 0.10;  // %10 indirim

    public function getName(): string
    {
        return '20 TL Üzeri %10 İndirim';
    }

    public function isEligible(Quote $quote): bool
    {
        return $quote->getSubtotal() >= self::MINIMUM_AMOUNT;
    }

    public function apply(Quote $quote): float
    {
        return $quote->getSubtotal() * self::DISCOUNT_RATE;
    }

    public function distributeDiscount(Quote $quote): void
    {
        foreach ($quote->getItems() as $item) {
            $item->setDiscountAmount(round($item->getRowTotal() * self::DISCOUNT_RATE, 4));
        }
    }
}
