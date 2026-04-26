<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints;

class RemoveItemsRequest
{
    #[Constraints\NotBlank(message: 'items are required.')]
    #[Constraints\Count(min: 1, minMessage: 'At least one item is required.')]
    #[Constraints\All([
        new Constraints\Collection(
            fields: [
                'product_id' => [new Constraints\NotNull(), new Constraints\Positive()],
                'qty'        => new Constraints\Optional([new Constraints\Positive()]),
            ],
        ),
    ])]
    public array $items = [];
}
