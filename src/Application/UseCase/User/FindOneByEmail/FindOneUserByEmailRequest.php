<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\FindOneByEmail;

final readonly class FindOneUserByEmailRequest
{
    public function __construct(
        private string $email
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
