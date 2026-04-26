<?php

namespace App\Controller;

use App\Exception\InsufficientStockException;
use App\Exception\ProductNotFoundException;
use App\Exception\QuoteNotFoundException;
use App\Request\AddItemsRequest;
use App\Request\RemoveItemsRequest;
use App\Security\ApiUser;
use App\Service\QuoteService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/quote')]
#[OA\Tag(name: 'Quote')]
class QuoteController extends AbstractController
{
    private const array GROUPS = ['groups' => ['quote:read', 'quote_item:read', 'product:read']];

    public function __construct(
        private readonly QuoteService $quoteService,
    ) {}

    #[Route('/add-items', methods: ['POST'])]
    #[OA\Post(
        summary: 'Add items to quote',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AddItemsRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Items added', content: new OA\JsonContent(ref: '#/components/schemas/QuoteResponse')),
            new OA\Response(response: 404, description: 'Product not found', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Insufficient stock', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function addItems(#[MapRequestPayload] AddItemsRequest $request): JsonResponse
    {
        /** @var ApiUser $user */
        $user = $this->getUser();

        try {
            $quote = $this->quoteService->addItems($user->getCustomerId(), $request->items);
        } catch (ProductNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (InsufficientStockException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json($quote, context: self::GROUPS);
    }

    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get active quote',
        responses: [
            new OA\Response(response: 200, description: 'Active quote', content: new OA\JsonContent(ref: '#/components/schemas/QuoteResponse')),
            new OA\Response(response: 404, description: 'No active quote found', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function getQuote(): JsonResponse
    {
        /** @var ApiUser $user */
        $user = $this->getUser();

        try {
            $quote = $this->quoteService->getQuote($user->getCustomerId());
        } catch (QuoteNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $this->json($quote, context: self::GROUPS);
    }

    #[Route('/remove-items', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Remove items from quote',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RemoveItemsRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns updated quote, or a message if quote is empty.',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(ref: '#/components/schemas/QuoteResponse'),
                        new OA\Schema(ref: '#/components/schemas/MessageResponse'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Quote or product not found', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function removeItems(#[MapRequestPayload] RemoveItemsRequest $request): JsonResponse
    {
        /** @var ApiUser $user */
        $user = $this->getUser();

        try {
            $quote = $this->quoteService->removeItems($user->getCustomerId(), $request->items);
        } catch (QuoteNotFoundException | ProductNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        if (!$quote) {
            return new JsonResponse(['message' => 'Quote is now empty and has been removed.']);
        }

        return $this->json($quote, context: self::GROUPS);
    }
}
