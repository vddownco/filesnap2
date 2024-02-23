<?php
declare(strict_types=1);

namespace App\Application\Domain\Entity\Snap;

use App\Application\Domain\Attribute\DomainEntity;
use App\Application\Domain\Attribute\Persistence;
use App\Application\Domain\Entity\Snap\FileStorage\File;
use DateTimeInterface;
use Symfony\Component\Uid\Uuid;

#[DomainEntity]
final class Snap
{
    public function __construct(
        #[Persistence] private readonly Uuid $id,
        #[Persistence] private readonly Uuid $userId,
        #[Persistence] private readonly string $originalFilename,
        #[Persistence] private readonly MimeType $mimeType,
        #[Persistence] private readonly DateTimeInterface $creationDate,
        #[Persistence] private ?DateTimeInterface $lastSeenDate,
        private readonly File $file
    )
    {
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

    public function getCreationDate(): DateTimeInterface
    {
        return $this->creationDate;
    }

    public function getLastSeenDate(): ?DateTimeInterface
    {
        return $this->lastSeenDate;
    }

    public function setLastSeenDate(?DateTimeInterface $lastSeenDate): self
    {
        $this->lastSeenDate = $lastSeenDate;
        return $this;
    }

    public function getFile(): File
    {
        return $this->file;
    }

    public function isImage(): bool
    {
        return in_array($this->mimeType, MimeType::imageMimeTypes, true);
    }

    public function isVideo(): bool
    {
        return in_array($this->mimeType, MimeType::videoMimeTypes, true);
    }
}