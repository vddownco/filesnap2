<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\Create;

use Symfony\Component\Uid\Uuid;

final readonly class CreateSnapRequest
{
    public function __construct(
        private Uuid $userId,
        private string $fileOriginalName,
        private string $fileMimeType,
        private string $filePath,
        private int $fileBytesSize
    ) {
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getFileOriginalName(): string
    {
        return $this->fileOriginalName;
    }

    public function getFileMimeType(): string
    {
        return $this->fileMimeType;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getFileBytesSize(): int
    {
        return $this->fileBytesSize;
    }
}
