<?php

declare(strict_types=1);

namespace App\Infrastructure\Entity\User\Service;

use App\Application\Domain\Entity\User\Service\PasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final class SymfonyPasswordHasher implements PasswordHasherInterface
{
    private static ?PasswordHasherFactoryInterface $factory = null;

    public function hash(string $plainPassword): string
    {
        return $this
            ->getFactory()
            ->getPasswordHasher('default')
            ->hash($plainPassword);
    }

    private function getFactory(): PasswordHasherFactoryInterface
    {
        if (self::$factory !== null) {
            return self::$factory;
        }

        self::$factory = new PasswordHasherFactory([
            'default' => ['algorithm' => 'auto'],
        ]);

        return self::$factory;
    }
}
