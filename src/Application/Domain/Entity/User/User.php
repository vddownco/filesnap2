<?php

declare(strict_types=1);

namespace App\Application\Domain\Entity\User;

use App\Application\Domain\Attribute\DomainEntity;
use App\Application\Domain\Attribute\Persistence;
use Symfony\Component\Uid\Uuid;

#[DomainEntity]
final class User
{
    /**
     * @param UserRole[] $roles
     */
    public function __construct(
        #[Persistence] private readonly Uuid $id,
        #[Persistence] private string $email,
        #[Persistence] private string $password,
        #[Persistence] private array $roles,
        #[Persistence] private Uuid $authorizationKey
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

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return UserRole[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param UserRole[] $roles
     */
    public function setRoles(array $roles): self
    {
        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    public function addRole(UserRole $role): self
    {
        if (in_array($role, $this->roles, true) === false) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function getAuthorizationKey(): Uuid
    {
        return $this->authorizationKey;
    }

    public function setAuthorizationKey(Uuid $authorizationKey): self
    {
        $this->authorizationKey = $authorizationKey;

        return $this;
    }
}
