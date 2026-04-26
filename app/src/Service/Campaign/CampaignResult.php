<?php

namespace App\Service\Campaign;

class CampaignResult
{
    public function __construct(
        public readonly ?string $appliedCampaignName,
        public readonly float $discountAmount,
    ) {}
}
