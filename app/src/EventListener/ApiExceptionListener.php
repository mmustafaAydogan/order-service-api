<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();

        if ($exception instanceof AuthenticationException) {
            $event->setResponse(new JsonResponse(
                ['error' => 'Unauthorized. Please provide a valid API key.'],
                Response::HTTP_UNAUTHORIZED
            ));

            return;
        }

        if ($exception instanceof AccessDeniedException) {
            $event->setResponse(new JsonResponse(
                ['error' => 'Access denied. Make sure your API key has the required permissions.'],
                Response::HTTP_FORBIDDEN
            ));

            return;
        }

        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        $event->setResponse(new JsonResponse(
            ['error' => $exception->getMessage()],
            $statusCode
        ));
    }
}
