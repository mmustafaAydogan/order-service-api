<?php

namespace App\Repository\Order;

use App\Entity\Order\OrderSequence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrderSequenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderSequence::class);
    }

    public function nextValue(): OrderSequence
    {
        $sequence = new OrderSequence();
        $this->getEntityManager()->persist($sequence);
        $this->getEntityManager()->flush();

        return $sequence;
    }
}
