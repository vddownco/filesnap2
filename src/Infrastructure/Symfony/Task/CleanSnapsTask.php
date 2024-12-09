<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Task;

use App\Application\UseCase\Snap\DeleteExpired\DeleteExpiredSnapsRequest;
use App\Application\UseCase\Snap\DeleteExpired\DeleteExpiredSnapsUseCase;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsPeriodicTask(frequency: '1 hour')]
final readonly class CleanSnapsTask
{
    public function __construct(
        private DeleteExpiredSnapsUseCase $deleteUnusedSnapsUseCase,
    ) {
    }

    /**
     * @throws \DateInvalidOperationException
     */
    public function __invoke(): void
    {
        ($this->deleteUnusedSnapsUseCase)(new DeleteExpiredSnapsRequest(new \DateTimeImmutable()));
    }
}
