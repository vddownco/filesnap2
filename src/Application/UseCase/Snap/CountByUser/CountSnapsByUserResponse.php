<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\CountByUser;

use App\Application\Domain\Entity\Snap\Snap;

final readonly class CountSnapsByUserResponse
{
    public function __construct(private int $count)
    {
    }

    public function getCount(): int
    {
        return $this->count;
    }
}