<?php

declare(strict_types=1);

namespace App\Application\Domain\Entity\Snap\Repository;

use App\Application\Domain\Entity\Snap\Snap;
use Symfony\Component\Uid\Uuid;

interface SnapRepositoryInterface
{
    public function create(Snap $snap): void;

    public function updateLastSeenDate(Uuid $id, \DateTimeInterface $lastSeenDate): void;

    public function findOneById(Uuid $id): ?Snap;

    /**
     * @param list<Uuid> $ids
     *
     * @return list<Snap>
     */
    public function findByIds(array $ids): array;

    /**
     * @return list<Snap>
     */
    public function findByUser(Uuid $userId, int $offset, int $limit): array;

    public function countByUser(Uuid $userId): int;

    public function deleteOneById(Uuid $id): void;

    /**
     * @param list<Uuid> $ids
     */
    public function deleteByIds(Uuid $userId, array $ids): void;
}
