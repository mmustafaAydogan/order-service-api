<?php

namespace App\Entity\Order;

use App\Enum\AddressType;
use App\Repository\Order\OrderAddressRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OrderAddressRepository::class)]
#[ORM\Table(name: 'order_addresses')]
#[ORM\Index(columns: ['order_id'], name: 'idx_order_addresses_order_id')]
#[ORM\Cache(usage: 'READ_ONLY')]
class OrderAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order_address:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'addresses')]
    #[ORM\JoinColumn(nullable: false)]
    private Order $order;

    #[ORM\Column(type: 'string', length: 10, enumType: AddressType::class)]
    #[Groups(['order_address:read'])]
    private AddressType $addressType;

    #[ORM\Column(length: 100)]
    #[Groups(['order_address:read'])]
    private string $firstName;

    #[ORM\Column(length: 100)]
    #[Groups(['order_address:read'])]
    private string $lastName;

    #[ORM\Column(length: 20)]
    #[Groups(['order_address:read'])]
    private string $phone;

    #[ORM\Column(length: 255)]
    #[Groups(['order_address:read'])]
    private string $addressLine;

    #[ORM\Column(length: 100)]
    #[Groups(['order_address:read'])]
    private string $district;

    #[ORM\Column(length: 100)]
    #[Groups(['order_address:read'])]
    private string $city;

    #[ORM\Column(length: 10)]
    #[Groups(['order_address:read'])]
    private string $postalCode;

    #[ORM\Column(length: 3)]
    #[Groups(['order_address:read'])]
    private string $countryCode = 'TR';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getAddressType(): AddressType
    {
        return $this->addressType;
    }

    public function setAddressType(AddressType $addressType): static
    {
        $this->addressType = $addressType;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddressLine(): string
    {
        return $this->addressLine;
    }

    public function setAddressLine(string $addressLine): static
    {
        $this->addressLine = $addressLine;

        return $this;
    }

    public function getDistrict(): string
    {
        return $this->district;
    }

    public function setDistrict(string $district): static
    {
        $this->district = $district;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): static
    {
        $this->countryCode = $countryCode;

        return $this;
    }
}
