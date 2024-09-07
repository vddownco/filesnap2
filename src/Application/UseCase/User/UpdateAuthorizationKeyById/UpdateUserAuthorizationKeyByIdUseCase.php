<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\UpdateAuthorizationKeyById;

use App\Application\Domain\User\UserRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final readonly class UpdateUserAuthorizationKeyByIdUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(UpdateUserAuthorizationKeyByIdRequest $request): UpdateUserAuthorizationKeyByIdResponse
    {
        $newAuthorizationKey = Uuid::v4();

        $this->userRepository->updateAuthorizationKey(
            $request->getId(),
            $newAuthorizationKey
        );

        return new UpdateUserAuthorizationKeyByIdResponse($newAuthorizationKey);
    }
}
