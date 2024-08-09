<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\FindByUser;

use App\Application\Domain\Snap\Snap;

final readonly class FindSnapsByUserResponse
{
    /**
     * @param list<Snap> $snaps
     */
    public function __construct(
        private array $snaps,
        private int $totalCount
    ) {
    }

    /**
     * @return list<Snap>
     */
    public function getSnaps(): array
    {
        return $this->snaps;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }
}
