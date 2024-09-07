<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\UpdatePasswordById;

use Symfony\Component\Uid\Uuid;

final readonly class UpdateUserPasswordByIdRequest
{
    public function __construct(
        private Uuid $id,
        private string $hashedPassword,
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getHashedPassword(): string
    {
        return $this->hashedPassword;
    }
}
