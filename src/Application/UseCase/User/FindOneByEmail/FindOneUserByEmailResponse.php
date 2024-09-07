<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\FindOneByEmail;

use App\Application\Domain\User\User;

final readonly class FindOneUserByEmailResponse
{
    public function __construct(
        private ?User $user,
    ) {
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
