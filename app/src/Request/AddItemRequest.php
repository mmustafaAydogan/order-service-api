<?php

namespace App\Request;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints;

class AddItemRequest
{
    #[SerializedName('product_id')]
    #[Constraints\NotNull(message: 'product_id is required.')]
    #[Constraints\Positive(message: 'product_id must be a positive integer.')]
    public int $productId;

    #[Constraints\NotNull(message: 'qty is required.')]
    #[Constraints\Positive(message: 'qty must be a positive integer.')]
    public int $qty;
}
