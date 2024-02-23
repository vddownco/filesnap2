<?php
declare(strict_types=1);

namespace App\Application\UseCase\User\Create;

use App\Application\Domain\Entity\User\User;

final readonly class CreateUserResponse
{
    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}