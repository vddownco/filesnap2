<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\DeleteExpired;

use Symfony\Component\Uid\Uuid;

final readonly class DeleteExpiredSnapsResponse
{
    /**
     * @param list<Uuid> $deletedSnapsIds
     */
    public function __construct(
        public array $deletedSnapsIds,
        public int $deletedCount,
    ) {
    }
}
