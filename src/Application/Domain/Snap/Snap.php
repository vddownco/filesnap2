<?php

declare(strict_types=1);

namespace App\Application\Domain\Snap;

use App\Application\Domain\Snap\FileStorage\File;
use Symfony\Component\Uid\Uuid;

final readonly class Snap
{
    public function __construct(
        private Uuid $id,
        private Uuid $userId,
        private string $originalFilename,
        private MimeType $mimeType,
        private \DateTimeInterface $creationDate,
        private ?\DateTimeInterface $lastSeenDate,
        private File $file,
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    public function getMimeType(): MimeType
    {
        return $this->mimeType;
    }

    public function getCreationDate(): \DateTimeInterface
    {
        return $this->creationDate;
    }

    public function getLastSeenDate(): ?\DateTimeInterface
    {
        return $this->lastSeenDate;
    }

    public function getFile(): File
    {
        return $this->file;
    }

    public function isImage(): bool
    {
        return $this->getMimeType()->isImage();
    }

    public function isVideo(): bool
    {
        return $this->getMimeType()->isVideo();
    }
}
