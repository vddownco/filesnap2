<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\Create;

use App\Application\Domain\Entity\User\Repository\UserRepositoryInterface;
use App\Application\Domain\Entity\User\Service\PasswordHasherInterface;
use App\Application\Domain\Entity\User\User;
use Symfony\Component\Uid\Uuid;

final readonly class CreateUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $passwordHasher
    ) {
    }

    public function __invoke(CreateUserRequest $request): CreateUserResponse
    {
        $user = new User(
            id: Uuid::v7(),
            email: $request->getEmail(),
            password: $this->passwordHasher->hash($request->getPlainPassword()),
            roles: $request->getRoles(),
            authorizationKey: Uuid::v4()
        );

        $this->userRepository->save($user);

        return new CreateUserResponse($user);
    }
}
