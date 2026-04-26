<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly array $apiTokens,
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-API-Token');
    }

    public function authenticate(Request $request): Passport
    {
        $token = $request->headers->get('X-API-Token');

        if (!isset($this->apiTokens[$token])) {
            throw new CustomUserMessageAuthenticationException('Invalid API token.');
        }

        $customerId = $this->apiTokens[$token];

        return new SelfValidatingPassport(
            new UserBadge((string) $customerId, fn() => new ApiUser($customerId))
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(
            ['error' => $exception->getMessageKey()],
            Response::HTTP_UNAUTHORIZED
        );
    }

}
