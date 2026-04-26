<?php

namespace App\EventListener;

use App\Entity\RequestLog;
use App\Security\ApiUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestLogListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TokenStorageInterface $tokenStorage,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (!str_starts_with($path, '/api') || str_starts_with($path, '/api/doc') || $path === '/api-docs.html') {
            return;
        }

        $response = $event->getResponse();
        $token = $this->tokenStorage->getToken();
        $customerId = null;

        if ($token?->getUser() instanceof ApiUser) {
            $customerId = $token->getUser()->getCustomerId();
        }

        $log = new RequestLog();
        $content = $request->getContent();
        $queryParams = $request->query->all();
        $requestPayload = $content ?: ($queryParams ? json_encode($queryParams) : null);

        $log->setCustomerId($customerId);
        $log->setMethod($request->getMethod());
        $log->setPath($path);
        $log->setRequestBody($requestPayload);
        $log->setResponseBody($response->getContent() ?: null);
        $log->setStatusCode($response->getStatusCode());
        $log->setCreatedAt(new \DateTimeImmutable());

        try {
            $this->em->persist($log);
            $this->em->flush();
        } catch (\Throwable) {
            // logging should never break the main flow
        }
    }
}
