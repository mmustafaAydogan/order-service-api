<?php

namespace App\Controller;

use App\Exception\EmptyQuoteException;
use App\Exception\InsufficientStockException;
use App\Exception\OrderNotFoundException;
use App\Exception\QuoteNotFoundException;
use App\Security\ApiUser;
use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/orders')]
class OrderController extends AbstractController
{
    private const array GROUPS = ['groups' => ['order:read', 'order_item:read']];

    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    #[Route('/create', methods: ['POST'])]
    public function create(): JsonResponse
    {
        /** @var ApiUser $user */
        $user = $this->getUser();

        try {
            $order = $this->orderService->createFromQuote($user->getCustomerId());
        } catch (QuoteNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (EmptyQuoteException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (InsufficientStockException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json($order, Response::HTTP_CREATED, context: self::GROUPS);
    }

    #[Route('/list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        /** @var ApiUser $user */
        $user = $this->getUser();

        $orders = $this->orderService->getOrders($user->getCustomerId());

        return $this->json($orders, context: self::GROUPS);
    }

    #[Route('/show/{orderNumber}', methods: ['GET'])]
    public function show(string $orderNumber): JsonResponse
    {
        /** @var ApiUser $user */
        $user = $this->getUser();

        try {
            $order = $this->orderService->getOrder($user->getCustomerId(), $orderNumber);
        } catch (OrderNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $this->json($order, context: self::GROUPS);
    }
}
