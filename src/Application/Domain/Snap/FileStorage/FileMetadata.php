<?php

declare(strict_types=1);

namespace App\Application\Domain\Snap\FileStorage;

use App\Application\Domain\Snap\MimeType;

final readonly class FileMetadata
{
    public function __construct(
        private string $originalName,
        private string $path,
        private MimeType $mimeType,
    ) {
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMimeType(): MimeType
    {
        return $this->mimeType;
    }
}
