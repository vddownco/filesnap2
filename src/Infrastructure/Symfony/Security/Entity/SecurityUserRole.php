<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Security\Entity;

use App\Application\Domain\User\UserRole;

enum SecurityUserRole: string
{
    case User = 'ROLE_USER';
    case Admin = 'ROLE_ADMIN';

    public static function fromUserRole(UserRole $userRole): self
    {
        return match ($userRole) {
            UserRole::User => self::User,
            UserRole::Admin => self::Admin
        };
    }

    public static function valueToUserRole(string $value): UserRole
    {
        return match ($value) {
            self::User->value => UserRole::User,
            self::Admin->value => UserRole::Admin,
            default => throw new \InvalidArgumentException(sprintf('%s does not have a matching %s.', $value, UserRole::class))
        };
    }

    public static function userRoleToValue(UserRole $userRole): string
    {
        return match ($userRole) {
            UserRole::User => self::User->value,
            UserRole::Admin => self::Admin->value
        };
    }
}
