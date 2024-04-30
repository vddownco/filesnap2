<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\UpdateLastSeenDate;

use Symfony\Component\Uid\Uuid;

final readonly class UpdateSnapLastSeenDateRequest
{
    public function __construct(
        private Uuid $id,
        private \DateTimeInterface $lastSeenDate
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getLastSeenDate(): \DateTimeInterface
    {
        return $this->lastSeenDate;
    }
}
