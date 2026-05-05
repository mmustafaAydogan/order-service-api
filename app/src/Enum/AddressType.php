<?php

namespace App\Enum;

enum AddressType: string
{
    case Billing = 'billing';
    case Shipping = 'shipping';
}
