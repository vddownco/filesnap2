<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\UpdateEmailById;

use Symfony\Component\Uid\Uuid;

final readonly class UpdateUserEmailByIdRequest
{
    public function __construct(
        private Uuid $id,
        private string $email,
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
