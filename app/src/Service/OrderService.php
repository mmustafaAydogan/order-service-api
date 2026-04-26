<?php

namespace App\Service;

use App\Entity\Order\Order;
use App\Entity\Order\OrderItem;
use App\Exception\EmptyQuoteException;
use App\Exception\InsufficientStockException;
use App\Exception\OrderNotFoundException;
use App\Exception\QuoteNotFoundException;
use App\Repository\Order\OrderRepository;
use App\Repository\Order\OrderSequenceRepository;
use App\Repository\ProductRepository;
use App\Repository\Quote\QuoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly QuoteRepository $quoteRepository,
        private readonly OrderRepository $orderRepository,
        private readonly OrderSequenceRepository $orderSequenceRepository,
        private readonly ProductRepository $productRepository,
        private readonly CacheItemPoolInterface $redisCache,
    ) {}

    public function createFromQuote(int $customerId): Order
    {
        $quote = $this->quoteRepository->findOneBy(['customerId' => $customerId]);

        if (!$quote) {
            throw new QuoteNotFoundException('No active quote found.');
        }

        if ($quote->getItems()->isEmpty()) {
            throw new EmptyQuoteException('Quote has no items.');
        }

        $this->em->beginTransaction();

        try {
            $sequence = $this->orderSequenceRepository->nextValue();

            $order = new Order();
            $order->setSequence($sequence);
            $order->setOrderNumber('ORD' . str_pad((string) $sequence->getSequenceValue(), 7, '0', STR_PAD_LEFT));
            $order->setCustomerId($customerId);
            $order->setSubtotal($quote->getSubtotal());
            $order->setDiscountAmount($quote->getDiscountAmount());
            $order->setShippingAmount($quote->getShippingAmount());
            $order->setGrandTotal($quote->getGrandTotal());
            $order->setAppliedCampaignName($quote->getAppliedCampaignName());

            $productIds = array_map(
                fn($item) => $item->getProduct()->getId(),
                $quote->getItems()->toArray()
            );

            $this->productRepository->findByIdsWithLock($productIds);

            foreach ($quote->getItems() as $quoteItem) {
                $product = $quoteItem->getProduct();

                if ($quoteItem->getQty() > $product->getStockQuantity()) {
                    throw new InsufficientStockException(
                        sprintf('Insufficient stock for "%s". Available Stock: %d.', $product->getTitle(), $product->getStockQuantity())
                    );
                }

                $product->setStockQuantity($product->getStockQuantity() - $quoteItem->getQty());

                $orderItem = new OrderItem();
                $orderItem->setOrder($order);
                $orderItem->setProductId($product->getId());
                $orderItem->setProductTitle($product->getTitle());
                $orderItem->setQty($quoteItem->getQty());
                $orderItem->setPrice(round($quoteItem->getPrice(), 4));
                $orderItem->setRowTotal(round($quoteItem->getRowTotal(), 4));
                $orderItem->setDiscountAmount(round($quoteItem->getDiscountAmount(), 4));
                $order->addItem($orderItem);
                $this->em->persist($orderItem);
            }

            $this->em->persist($order);
            $this->em->remove($quote);
            $this->em->flush();
            $this->em->commit();

            return $order;
        } catch (\Throwable $throwable) {
            $this->em->rollback();
            throw $throwable;
        }
    }

    public function getOrders(int $customerId, int $page, int $limit): array
    {
        $paginator = $this->orderRepository->findPaginatedByCustomerId($customerId, $page, $limit);
        $total = count($paginator);

        return [
            'orders'  => iterator_to_array($paginator),
            'total' => $total,
            'page'  => $page,
            'limit' => $limit,
            'pages' => (int) ceil($total / $limit),
        ];
    }

    public function getOrder(int $customerId, string $orderNumber): Order
    {
        $cacheKey = sprintf('order_%d_%s', $customerId, $orderNumber);

        $item = $this->redisCache->getItem($cacheKey);

        if ($item->isHit()) {
            return $item->get();
        }

        $order = $this->findOrder($customerId, $orderNumber);
        $item->set($order)->expiresAfter(3600);
        $this->redisCache->save($item);

        return $order;
    }

    private function findOrder(int $customerId, string $orderNumber): Order
    {
        $order = $this->orderRepository->findOneBy([
            'orderNumber' => $orderNumber,
            'customerId'  => $customerId,
        ]);

        if (!$order) {
            throw new OrderNotFoundException('Order not found.');
        }
        $order->getItems();

        return $order;
    }
}
