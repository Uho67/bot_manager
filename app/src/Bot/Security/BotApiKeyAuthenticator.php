<?php

/**
 * Copyright © Dmytro Ushchenko. All rights reserved.
 */

declare(strict_types=1);

namespace App\Bot\Security;

use App\Bot\Repository\BotRepository;
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

class BotApiKeyAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly BotRepository $botRepository,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        // Only support requests to catalog API endpoints
        return str_starts_with($request->getPathInfo(), '/api/catalog/');
    }

    public function authenticate(Request $request): Passport
    {
        $apiKey = $request->headers->get('Authorization');

        if (null === $apiKey) {
            throw new CustomUserMessageAuthenticationException('No API key provided');
        }

        // Remove "Bearer " prefix if present
        if (str_starts_with($apiKey, 'Bearer ')) {
            $apiKey = substr($apiKey, 7);
        }

        $bot = $this->botRepository->findByApiKey($apiKey);

        if (null === $bot) {
            throw new CustomUserMessageAuthenticationException('Invalid API key');
        }

        // Store bot identifier in request attributes for later use
        $request->attributes->set('bot_identifier', $bot->getBotIdentifier());

        return new SelfValidatingPassport(
            new UserBadge($bot->getBotIdentifier())
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // On success, let the request continue
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

