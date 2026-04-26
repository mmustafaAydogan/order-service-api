<?php

namespace App\Service\Campaign\Rule;

use App\Entity\Quote\Quote;
use App\Service\Campaign\CampaignInterface;

class AuthorDiscountCampaign implements CampaignInterface
{
    private const AUTHOR_ID = 3;        // Sabahattin Ali
    private const DISCOUNT_RATE = 0.20; // %20 indirim

    public function getName(): string
    {
        return 'Sabahattin Ali %20 İndirim';
    }

    public function isEligible(Quote $quote): bool
    {
        foreach ($quote->getItems() as $item) {
            if ($item->getProduct()->getAuthor()->getId() === self::AUTHOR_ID) {
                return true;
            }
        }

        return false;
    }

    public function apply(Quote $quote): float
    {
        $discount = 0;

        foreach ($quote->getItems() as $item) {
            if ($item->getProduct()->getAuthor()->getId() === self::AUTHOR_ID) {
                $discount += $item->getRowTotal() * self::DISCOUNT_RATE;
            }
        }

        return $discount;
    }

    public function distributeDiscount(Quote $quote): void
    {
        foreach ($quote->getItems() as $item) {
            if ($item->getProduct()->getAuthor()->getId() === self::AUTHOR_ID) {
                $item->setDiscountAmount(round($item->getRowTotal() * self::DISCOUNT_RATE, 4));
            }
        }
    }
}
