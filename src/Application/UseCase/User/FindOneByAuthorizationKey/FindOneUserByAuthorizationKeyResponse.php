<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\FindOneByAuthorizationKey;

use App\Application\Domain\Entity\User\User;

final readonly class FindOneUserByAuthorizationKeyResponse
{
    public function __construct(private ?User $user)
    {
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
