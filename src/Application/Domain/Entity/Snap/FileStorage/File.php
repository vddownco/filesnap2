<?php
declare(strict_types=1);

namespace App\Application\Domain\Entity\Snap\FileStorage;

final readonly class File
{
    public function __construct(private string $absolutePath)
    {
    }

    public function getAbsolutePath(): string
    {
        return $this->absolutePath;
    }
}