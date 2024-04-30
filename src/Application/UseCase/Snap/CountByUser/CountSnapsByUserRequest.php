<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\CountByUser;

use Symfony\Component\Uid\Uuid;

final readonly class CountSnapsByUserRequest
{
    public function __construct(private Uuid $userId)
    {
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }
}
