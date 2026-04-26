<?php

namespace App\Service\Campaign;

use App\Entity\Quote\Quote;

interface CampaignInterface
{
    public function getName(): string;

    public function isEligible(Quote $quote): bool;

    public function apply(Quote $quote): float;

    public function distributeDiscount(Quote $quote): void;
}
