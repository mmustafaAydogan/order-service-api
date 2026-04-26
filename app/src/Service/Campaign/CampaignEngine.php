<?php

namespace App\Service\Campaign;

use App\Entity\Quote\Quote;

class CampaignEngine
{
    /** @param CampaignInterface[] $campaigns */
    public function __construct(
        private readonly iterable $campaigns,
    ) {}

    public function evaluate(Quote $quote): CampaignResult
    {
        $bestDiscount = 0;
        $bestCampaign = null;

        foreach ($this->campaigns as $campaign) {
            if (!$campaign->isEligible($quote)) {
                continue;
            }

            $discount = $campaign->apply($quote);

            if ($discount > $bestDiscount) {
                $bestDiscount = $discount;
                $bestCampaign = $campaign;
            }
        }

        foreach ($quote->getItems() as $item) {
            $item->setDiscountAmount(0.0);
        }

        if ($bestCampaign !== null) {
            $bestCampaign->distributeDiscount($quote);
        }

        return new CampaignResult($bestCampaign?->getName(), round($bestDiscount, 4));
    }
}
