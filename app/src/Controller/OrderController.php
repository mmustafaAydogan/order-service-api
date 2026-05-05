<?php

namespace App\Controller;

use App\Exception\EmptyQuoteException;
use App\Exception\InsufficientStockException;
use App\Exception\MissingBillingAddressException;
use App\Exception\OrderNotFoundException;
use App\Exception\QuoteNotFoundException;
use App\Security\ApiUser;
use App\Service\OrderService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/orders')]
#[OA\Tag(name: 'Order')]
class OrderController extends AbstractController
{
    private const array GROUPS = ['groups' => ['order:read', 'order_item:read', 'order_address:read']];

    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    #[Route('/create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create order from active quote',
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateOrderRequest')
        ),
        responses: [
            new OA\Response(response: 201, description: 'Order created', content: new OA\JsonContent(ref: '#/components/schemas/OrderResponse')),
            new OA\Response(response: 400, description: 'Quote is empty', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'No active quote found', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 424, description: 'No billing address found for this customer', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Insufficient stock', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        /** @var ApiUser $user */
        $user = $this->getUser();

        $body     = json_decode($request->getContent() ?: '{}', true) ?? [];
        $billing  = $body['billing_address']  ?? null;
        $shipping = $body['shipping_address'] ?? null;

        try {
            $order = $this->orderService->createFromQuote($user->getCustomerId(), $billing, $shipping);
        } catch (QuoteNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (EmptyQuoteException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (InsufficientStockException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (MissingBillingAddressException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_FAILED_DEPENDENCY);
        }

        return $this->json($order, Response::HTTP_CREATED, context: self::GROUPS);
    }

    #[Route('/list', methods: ['GET'])]
    #[OA\Get(
        summary: 'List all orders',
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)),
            new OA\Parameter(name: 'limit', description: 'Max 100', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 10, minimum: 1, maximum: 100)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of orders',
                content: new OA\JsonContent(ref: '#/components/schemas/PaginatedOrderResponse')
            ),
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        /** @var ApiUser $user */
        $user = $this->getUser();

        $page  = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 10)));

        $result = $this->orderService->getOrders($user->getCustomerId(), $page, $limit);

        return $this->json($result, context: self::GROUPS);
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
