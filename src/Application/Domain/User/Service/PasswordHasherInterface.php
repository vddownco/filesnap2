<?php

declare(strict_types=1);

namespace App\Application\Domain\User\Service;

interface PasswordHasherInterface
{
    /**
     * @param string $plainPassword
     * @return non-empty-string
     */
    public function hash(string $plainPassword): string;
}
