<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\FindByUser;

use Symfony\Component\Uid\Uuid;

final readonly class FindSnapsByUserRequest
{
    public function __construct(
        private Uuid $userId,
        private int $offset,
        private int $limit,
    ) {
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
