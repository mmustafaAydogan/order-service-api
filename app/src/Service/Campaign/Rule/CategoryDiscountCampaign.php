<?php

namespace App\Service\Campaign\Rule;

use App\Entity\Quote\Quote;
use App\Service\Campaign\CampaignInterface;

class CategoryDiscountCampaign implements CampaignInterface
{
    private const CATEGORY_ID = 1;   // Roman kategorisi
    private const DISCOUNT_RATE = 0.15; // %15 indirim

    public function getName(): string
    {
        return 'Roman Kategorisi %15 İndirim';
    }

    public function isEligible(Quote $quote): bool
    {
        foreach ($quote->getItems() as $item) {
            if ($item->getProduct()->getCategory()->getId() === self::CATEGORY_ID) {
                return true;
            }
        }

        return false;
    }

    public function apply(Quote $quote): float
    {
        $discount = 0;

        foreach ($quote->getItems() as $item) {
            if ($item->getProduct()->getCategory()->getId() === self::CATEGORY_ID) {
                $discount += $item->getRowTotal() * self::DISCOUNT_RATE;
            }
        }

        return $discount;
    }

    public function distributeDiscount(Quote $quote): void
    {
        foreach ($quote->getItems() as $item) {
            if ($item->getProduct()->getCategory()->getId() === self::CATEGORY_ID) {
                $item->setDiscountAmount(round($item->getRowTotal() * self::DISCOUNT_RATE, 4));
            }
        }
    }
}
