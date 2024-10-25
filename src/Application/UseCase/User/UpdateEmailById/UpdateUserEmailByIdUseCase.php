<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\UpdateEmailById;

use App\Application\Domain\User\Exception\AlreadyExistingUserWithEmail;
use App\Application\Domain\User\Exception\EmailIsUserCurrentEmail;
use App\Application\Domain\User\UserRepositoryInterface;

final readonly class UpdateUserEmailByIdUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * @throws AlreadyExistingUserWithEmail
     * @throws EmailIsUserCurrentEmail
     */
    public function __invoke(UpdateUserEmailByIdRequest $request): void
    {
        $user = $this->userRepository->findOneByEmail($request->getEmail());

        if ($user !== null) {
            if ($user->getId()->toRfc4122() === $request->getId()->toRfc4122()) {
                throw new EmailIsUserCurrentEmail($request->getEmail());
            }

            throw new AlreadyExistingUserWithEmail($request->getEmail());
        }

        $this->userRepository->updateEmail($request->getId(), $request->getEmail());
    }
}
