<?php

declare(strict_types=1);

namespace App\Application\Domain\User;

use Symfony\Component\Uid\Uuid;

final readonly class User
{
    /**
     * @param list<UserRole> $roles
     */
    public function __construct(
        private Uuid $id,
        private string $email,
        private string $password,
        private array $roles,
        private Uuid $authorizationKey
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

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return list<UserRole>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getAuthorizationKey(): Uuid
    {
        return $this->authorizationKey;
    }
}
