<?php

declare(strict_types=1);

namespace App\Application\Domain\Snap;

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
    public function findByUser(Uuid $userId, int $offset, int $limit, ?\DateTimeInterface $expirationCheckDate = null): array;

    public function countByUser(Uuid $userId, ?\DateTimeInterface $expirationCheckDate = null): int;

    public function deleteOneById(Uuid $id): void;

    /**
     * @param list<Uuid> $ids
     */
    public function deleteByIds(array $ids): void;

    /**
     * @return list<Snap>
     */
    public function findExpiredSnaps(\DateTimeInterface $date): array;
}
