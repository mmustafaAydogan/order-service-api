<?php

namespace App\Repository\Order;

use App\Entity\Order\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findPaginatedByCustomerId(int $customerId, int $page, int $limit): Paginator
    {
        $qb = $this->createQueryBuilder('ord')
            ->where('ord.customerId = :customerId')
            ->setParameter('customerId', $customerId)
            ->orderBy('ord.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return new Paginator($qb);
    }
}
