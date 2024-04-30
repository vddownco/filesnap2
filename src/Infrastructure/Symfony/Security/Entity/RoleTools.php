<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Security\Entity;

use App\Application\Domain\Entity\User\UserRole;

final readonly class RoleTools
{
    public static function fromUserRoleToSecurityUserRole(UserRole $role): SecurityUserRole
    {
        return match ($role) {
            UserRole::User => SecurityUserRole::User,
            UserRole::Admin => SecurityUserRole::Admin
        };
    }

    public static function fromStringToUserRole(string $role): UserRole
    {
        return match ($role) {
            SecurityUserRole::User->value => UserRole::User,
            SecurityUserRole::Admin->value => UserRole::Admin
        };
    }

    public static function fromUserRoleToString(UserRole $role): string
    {
        return match ($role) {
            UserRole::User => SecurityUserRole::User->value,
            UserRole::Admin => SecurityUserRole::Admin->value
        };
    }

    public static function fromSecurityUserRoleToString(SecurityUserRole $role): string
    {
        return match ($role) {
            SecurityUserRole::User => SecurityUserRole::User->value,
            SecurityUserRole::Admin => SecurityUserRole::Admin->value
        };
    }
}
