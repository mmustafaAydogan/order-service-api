<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class ApiUser implements UserInterface
{
    public function __construct(
        private readonly int $customerId,
    ) {}

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->customerId;
    }

    public function getRoles(): array
    {
        return ['ROLE_API'];
    }
}
