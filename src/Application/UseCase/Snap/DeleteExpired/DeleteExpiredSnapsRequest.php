<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\DeleteExpired;

final readonly class DeleteExpiredSnapsRequest
{
    public function __construct(
        public \DateTimeInterface $date
    ) {
    }
}
