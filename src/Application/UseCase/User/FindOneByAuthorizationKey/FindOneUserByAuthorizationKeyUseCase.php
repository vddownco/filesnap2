<?php
declare(strict_types=1);

namespace App\Application\UseCase\User\FindOneByAuthorizationKey;

use App\Application\Domain\Entity\User\Repository\UserRepositoryInterface;

final readonly class FindOneUserByAuthorizationKeyUseCase
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function __invoke(FindOneUserByAuthorizationKeyRequest $request): FindOneUserByAuthorizationKeyResponse
    {
        return new FindOneUserByAuthorizationKeyResponse(
            $this->userRepository->findOneByAuthorizationKey($request->getAuthorizationKey())
        );
    }
}