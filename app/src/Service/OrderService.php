<?php

namespace App\Service;

use App\Helper\Address;
use App\Entity\Order\Order;
use App\Entity\Order\OrderItem;
use App\Exception\EmptyQuoteException;
use App\Exception\InsufficientStockException;
use App\Exception\MissingBillingAddressException;
use App\Exception\OrderNotFoundException;
use App\Exception\QuoteNotFoundException;
use App\Repository\CustomerAddressRepository;
use App\Repository\Order\OrderRepository;
use App\Repository\Order\OrderSequenceRepository;
use App\Repository\ProductRepository;
use App\Repository\Quote\QuoteRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly QuoteRepository $quoteRepository,
        private readonly OrderRepository $orderRepository,
        private readonly OrderSequenceRepository $orderSequenceRepository,
        private readonly ProductRepository $productRepository,
        private readonly CustomerAddressRepository $customerAddressRepository,
    ) {}

    public function createFromQuote(int $customerId, ?array $billing = null, ?array $shipping = null): Order
    {
        $quote = $this->quoteRepository->findOneBy(['customerId' => $customerId]);

        if (!$quote) {
            throw new QuoteNotFoundException('No active quote found.');
        }

        if ($quote->getItems()->isEmpty()) {
            throw new EmptyQuoteException('Quote has no items.');
        }

        $billingData  = $billing  ? $this->fillAddress($billing)  : $this->resolveCustomerAddress($customerId);
        $shippingData = $shipping ? $this->fillAddress($shipping) : $billingData;

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

            $order->addBillingAddress($billingData);
            $order->addShippingAddress($shippingData);

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
        $order = $this->orderRepository->findOneByOrderNumber($customerId, $orderNumber);

        if (!$order) {
            throw new OrderNotFoundException('Order not found.');
        }

        return $order;
    }

    private function resolveCustomerAddress(int $customerId): Address
    {
        $address = $this->customerAddressRepository->findByCustomerId($customerId);

        if (!$address) {
            throw new MissingBillingAddressException('No billing address found for this customer.');
        }

        return new Address(
            firstName:   $address->getFirstName(),
            lastName:    $address->getLastName(),
            phone:       $address->getPhone(),
            addressLine: $address->getAddressLine(),
            district:    $address->getDistrict(),
            city:        $address->getCity(),
            postalCode:  $address->getPostalCode(),
            countryCode: $address->getCountryCode(),
        );
    }

    private function fillAddress(array $data): Address
    {
        return new Address(
            firstName:   $data['first_name'],
            lastName:    $data['last_name'],
            phone:       $data['phone'],
            addressLine: $data['address_line'],
            district:    $data['district'],
            city:        $data['city'],
            postalCode:  $data['postal_code'],
            countryCode: $data['country_code'] ?? 'TR',
        );
    }

}
