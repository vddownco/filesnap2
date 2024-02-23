<?php
declare(strict_types=1);

namespace App\Application\Domain\Entity\Snap\Repository;

use App\Application\Domain\Entity\Snap\Snap;
use DateTimeInterface;
use Symfony\Component\Uid\Uuid;

interface SnapRepositoryInterface
{
    public function create(Snap $snap): void;

    public function updateLastSeenDate(Uuid $id, DateTimeInterface $lastSeenDate): void;

    public function findOneById(Uuid $id): ?Snap;

    /**
     * @return Snap[]
     */
    public function findByUser(Uuid $userId, int $offset, int $limit): array;

    public function countByUser(Uuid $userId): int;

    public function deleteOneById(Uuid $id): void;
}