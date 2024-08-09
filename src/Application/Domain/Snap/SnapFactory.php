<?php

declare(strict_types=1);

namespace App\Application\Domain\Snap;

use App\Application\Domain\Snap\Exception\FileNotFoundException;
use App\Application\Domain\Snap\FileStorage\FileStorageInterface;
use Symfony\Component\Uid\Uuid;

final readonly class SnapFactory
{
    public function __construct(
        private FileStorageInterface $fileStorage
    ) {
    }

    /**
     * @throws FileNotFoundException
     */
    public function create(
        Uuid $id,
        Uuid $userId,
        string $originalFilename,
        MimeType $mimeType,
        \DateTimeInterface $creationDate,
        ?\DateTimeInterface $lastSeenDate
    ): Snap {
        $file = $this->fileStorage->get($id, $userId);

        if ($file === null) {
            throw new FileNotFoundException($id);
        }

        return new Snap(
            id: $id,
            userId: $userId,
            originalFilename: $originalFilename,
            mimeType: $mimeType,
            creationDate: $creationDate,
            lastSeenDate: $lastSeenDate,
            file: $file
        );
    }
}
