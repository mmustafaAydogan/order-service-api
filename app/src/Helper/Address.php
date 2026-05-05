<?php

namespace App\Helper;

readonly class Address
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $phone,
        public string $addressLine,
        public string $district,
        public string $city,
        public string $postalCode,
        public string $countryCode = 'TR',
    ) {}
}
