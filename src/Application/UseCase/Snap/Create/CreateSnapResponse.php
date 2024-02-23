<?php
declare(strict_types=1);

namespace App\Application\UseCase\Snap\Create;

use App\Application\Domain\Entity\Snap\Snap;

final readonly class CreateSnapResponse
{
    public function __construct(private Snap $snap)
    {
    }

    public function getSnap(): Snap
    {
        return $this->snap;
    }
}