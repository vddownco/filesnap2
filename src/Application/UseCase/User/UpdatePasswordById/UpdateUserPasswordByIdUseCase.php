<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\UpdatePasswordById;

use App\Application\Domain\User\UserRepositoryInterface;

final readonly class UpdateUserPasswordByIdUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(UpdateUserPasswordByIdRequest $request): void
    {
        $this->userRepository->updatePassword($request->getId(), $request->getHashedPassword());
    }
}
