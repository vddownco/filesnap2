<?php

declare(strict_types=1);

namespace App\Application\Domain\Entity\Snap\FileStorage;

use App\Application\Domain\Entity\Snap\Snap;
use Symfony\Component\Uid\Uuid;

interface FileStorageInterface
{
    /**
     * The integer returned must represent a file size in bytes.
     */
    public function getFileMaximumAuthorizedBytesSize(): int;

    public function store(Uuid $snapId, Uuid $snapUserId, FileMetadata $fileMetadata): void;

    public function delete(Snap $snap): void;

    public function get(Uuid $snapId, Uuid $snapUserId): ?File;
}
