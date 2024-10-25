<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\UpdatePasswordById;

use Symfony\Component\Uid\Uuid;

final readonly class UpdateUserPasswordByIdRequest
{
    public function __construct(
        private Uuid $id,
        private string $password,
        private bool $passwordIsHashed,
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function passwordIsHashed(): bool
    {
        return $this->passwordIsHashed;
    }
}
