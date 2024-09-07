<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\FindOneByEmail;

use App\Application\Domain\User\UserRepositoryInterface;

final readonly class FindOneUserByEmailUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(FindOneUserByEmailRequest $request): FindOneUserByEmailResponse
    {
        return new FindOneUserByEmailResponse(
            $this->userRepository->findOneByEmail($request->getEmail())
        );
    }
}
