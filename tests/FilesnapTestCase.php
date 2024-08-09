<?php

declare(strict_types=1);

namespace App\Tests;

use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FilesnapTestCase extends KernelTestCase
{
    /**
     * @throws RandomException
     */
    protected static function getRandomInt(int $min = 0, int $max = 2000): int
    {
        return random_int($min, $max);
    }

    /**
     * @throws RandomException
     */
    protected static function getRandomDateTime(): \DateTimeInterface
    {
        return (new \DateTimeImmutable())->setTimestamp(random_int(0, 2000000000));
    }
}
