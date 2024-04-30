<?php

declare(strict_types=1);

namespace App\Application\Domain\Entity\User\Service;

interface PasswordHasherInterface
{
    public function hash(string $plainPassword): string;
}
