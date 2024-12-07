<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\Create;

use App\Application\Domain\User\UserRole;

final readonly class CreateUserRequest
{
    /**
     * @param non-empty-string $email
     * @param non-empty-string $plainPassword
     * @param list<UserRole> $roles
     */
    public function __construct(
        private string $email,
        private string $plainPassword,
        private array $roles,
    ) {
    }

    /**
     * @return non-empty-string
     */
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
