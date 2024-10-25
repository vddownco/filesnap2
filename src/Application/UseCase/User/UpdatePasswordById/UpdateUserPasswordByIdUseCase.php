<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\UpdatePasswordById;

use App\Application\Domain\User\Service\PasswordHasherInterface;
use App\Application\Domain\User\UserRepositoryInterface;

final readonly class UpdateUserPasswordByIdUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(UpdateUserPasswordByIdRequest $request): void
    {
        $password = $request->passwordIsHashed() === true
            ? $request->getPassword()
            : $this->passwordHasher->hash($request->getPassword());

        $this->userRepository->updatePassword($request->getId(), $password);
    }
}
