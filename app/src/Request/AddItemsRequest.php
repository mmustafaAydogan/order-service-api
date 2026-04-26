<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints;

class AddItemsRequest
{
    #[Constraints\NotNull(message: 'items is required.')]
    #[Constraints\Count(min: 1, minMessage: 'At least one item is required.')]
    #[Constraints\All(constraints: [
        new Constraints\Collection(
            fields: [
                'product_id' => [
                    new Constraints\NotNull(message: 'product_id is required.'),
                    new Constraints\Positive(message: 'product_id must be a positive integer.'),
                ],
                'qty' => [
                    new Constraints\NotNull(message: 'qty is required.'),
                    new Constraints\Positive(message: 'qty must be a positive integer.'),
                ],
            ],
            missingFieldsMessage: 'The field {{ field }} is missing.',
        ),
    ])]
    public array $items = [];
}
