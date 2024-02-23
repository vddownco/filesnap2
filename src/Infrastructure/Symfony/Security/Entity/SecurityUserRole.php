<?php
declare(strict_types=1);

namespace App\Infrastructure\Symfony\Security\Entity;

enum SecurityUserRole: string
{
    case User = 'ROLE_USER';
    case Admin = 'ROLE_ADMIN';
}