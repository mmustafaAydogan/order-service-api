<?php

namespace App\Controller;

use App\Exception\InsufficientStockException;
use App\Exception\ProductNotFoundException;
use App\Exception\QuoteNotFoundException;
use App\Request\AddItemRequest;
use App\Request\RemoveItemsRequest;
use App\Security\ApiUser;
use App\Service\QuoteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/quote')]
class QuoteController extends AbstractController
{
    private const array GROUPS = ['groups' => ['quote:read', 'quote_item:read', 'product:read']];

    public function __construct(
        private readonly QuoteService $quoteService,
    ) {}

    #[Route('/add-item', methods: ['POST'])]
    public function addItem(#[MapRequestPayload] AddItemRequest $request): JsonResponse
    {
        /** @var ApiUser $user */
        $user = $this->getUser();

        try {
            $quote = $this->quoteService->addItem($user->getCustomerId(), $request->productId, $request->qty);
        } catch (ProductNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (InsufficientStockException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json($quote, context: self::GROUPS);
    }

    #[Route('', methods: ['GET'])]
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
