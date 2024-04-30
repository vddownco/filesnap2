<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Security\Entity;

use App\Application\Domain\Entity\User\User;
use App\Application\Domain\Entity\User\UserRole;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

final class SecurityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @param SecurityUserRole[] $securityRoles
     */
    public function __construct(
        private Uuid $id,
        private string $email,
        private string $password,
        private array $securityRoles,
        private Uuid $authorizationKey
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): self
    {
        $this->id = $id;

        return $this;
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
     * @return string[]
     */
    public function getRoles(): array
    {
        return array_map(
            static fn (SecurityUserRole $role): string => RoleTools::fromSecurityUserRoleToString($role),
            $this->securityRoles
        );
    }

    /**
     * @param SecurityUserRole[] $securityRoles
     */
    public function setSecurityRoles(array $securityRoles): self
    {
        $this->securityRoles = $securityRoles;

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

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public static function create(User $user): self
    {
        return new self(
            $user->getId(),
            $user->getEmail(),
            $user->getPassword(),
            array_map(
                static fn (UserRole $role): SecurityUserRole => RoleTools::fromUserRoleToSecurityUserRole($role),
                $user->getRoles()
            ),
            $user->getAuthorizationKey()
        );
    }
}
