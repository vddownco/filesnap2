<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\DeleteUserSnaps;

use Symfony\Component\Uid\Uuid;

final readonly class DeleteUserSnapsRequest
{
    /**
     * @param list<Uuid> $snapIds
     */
    public function __construct(
        private Uuid $userId,
        private array $snapIds
    ) {
    }

    public function getUserId(): Uuid
    {
        return $this->userId;
    }

    /**
     * @return list<Uuid>
     */
    public function getSnapIds(): array
    {
        return $this->snapIds;
    }
}
