<?php
declare(strict_types=1);

namespace App\Application\UseCase\Snap\FindByUser;

use App\Application\Domain\Entity\Snap\Snap;

final readonly class FindSnapsByUserResponse
{
    /**
     * @param Snap[] $snaps
     */
    public function __construct(private array $snaps)
    {
    }

    /**
     * @return Snap[]
     */
    public function getSnaps(): array
    {
        return $this->snaps;
    }
}