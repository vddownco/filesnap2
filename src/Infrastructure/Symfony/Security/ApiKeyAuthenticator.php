<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Security;

use App\Application\UseCase\User\FindOneByAuthorizationKey\FindOneUserByAuthorizationKeyRequest;
use App\Application\UseCase\User\FindOneByAuthorizationKey\FindOneUserByAuthorizationKeyUseCase;
use App\Infrastructure\Symfony\Security\Entity\SecurityUser;
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
use Symfony\Component\Uid\Uuid;

final class ApiKeyAuthenticator extends AbstractAuthenticator
{
    public const string AUTHORIZATION_HEADER_PREFIX = 'ApiKey ';

    public function __construct(
        private readonly FindOneUserByAuthorizationKeyUseCase $findOneUserByAuthorizationKeyUseCase,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return true;
    }

    public function authenticate(Request $request): Passport
    {
        $authorizationHeader = $request->headers->get('Authorization');

        if (
            $authorizationHeader === null
            || str_starts_with($authorizationHeader, self::AUTHORIZATION_HEADER_PREFIX) === false
        ) {
            throw $this->createIncorrectApiKeyException();
        }

        $apiKey = substr($authorizationHeader, strlen(self::AUTHORIZATION_HEADER_PREFIX));

        try {
            $apiKeyUuid = Uuid::fromBase58($apiKey);
        } catch (\InvalidArgumentException) {
            throw $this->createIncorrectApiKeyException();
        }

        $useCaseResponse = ($this->findOneUserByAuthorizationKeyUseCase)(
            new FindOneUserByAuthorizationKeyRequest($apiKeyUuid)
        );

        $user = $useCaseResponse->getUser();

        if ($user === null) {
            throw $this->createIncorrectApiKeyException();
        }

        $userIdentifier = SecurityUser::create($user)->getUserIdentifier();

        return new SelfValidatingPassport(new UserBadge($userIdentifier));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED);
    }

    private function createIncorrectApiKeyException(): CustomUserMessageAuthenticationException
    {
        return new CustomUserMessageAuthenticationException('Incorrect API key.');
    }
}
