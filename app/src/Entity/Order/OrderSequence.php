<?php

namespace App\Entity\Order;

use App\Repository\Order\OrderSequenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderSequenceRepository::class)]
#[ORM\Table(name: 'order_sequence')]
class OrderSequence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $sequenceValue;

    #[ORM\OneToOne(targetEntity: Order::class, mappedBy: 'sequence')]
    private ?Order $order = null;

    public function getSequenceValue(): int
    {
        return $this->sequenceValue;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }
}
