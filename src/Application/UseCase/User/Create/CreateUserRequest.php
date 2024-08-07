<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\Create;

use App\Application\Domain\Entity\User\UserRole;

final readonly class CreateUserRequest
{
    /**
     * @param list<UserRole> $roles
     */
    public function __construct(
        private string $email,
        private string $plainPassword,
        private array $roles,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    /**
     * @return list<UserRole>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }
}
