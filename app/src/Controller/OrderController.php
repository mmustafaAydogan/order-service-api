<?php

namespace App\Controller;

use App\Exception\EmptyQuoteException;
use App\Exception\InsufficientStockException;
use App\Exception\OrderNotFoundException;
use App\Exception\QuoteNotFoundException;
use App\Security\ApiUser;
use App\Service\OrderService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/orders')]
#[OA\Tag(name: 'Order')]
class OrderController extends AbstractController
{
    private const array GROUPS = ['groups' => ['order:read', 'order_item:read']];

    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    #[Route('/create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create order from active quote',
        responses: [
            new OA\Response(response: 201, description: 'Order created', content: new OA\JsonContent(ref: '#/components/schemas/OrderResponse')),
            new OA\Response(response: 400, description: 'Quote is empty', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'No active quote found', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Insufficient stock', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
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
    #[OA\Get(
        summary: 'List all orders',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of orders',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/OrderResponse')
                )
            ),
        ]
    )]
    public function list(): JsonResponse
    {
        /** @var ApiUser $user */
        $user = $this->getUser();

        $orders = $this->orderService->getOrders($user->getCustomerId());

        return $this->json($orders, context: self::GROUPS);
    }

    #[Route('/show/{orderNumber}', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get order by order number',
        parameters: [
            new OA\Parameter(name: 'orderNumber', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'ORD0000001'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Order details', content: new OA\JsonContent(ref: '#/components/schemas/OrderResponse')),
            new OA\Response(response: 404, description: 'Order not found', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
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
