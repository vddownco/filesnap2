<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\DeleteExpired;

use App\Application\Domain\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Snap\Snap;
use App\Application\Domain\Snap\SnapRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final readonly class DeleteExpiredSnapsUseCase
{
    public function __construct(
        private SnapRepositoryInterface $snapRepository,
        private FileStorageInterface $fileStorage,
    ) {
    }

    /**
     * @throws \DateInvalidOperationException
     */
    public function __invoke(DeleteExpiredSnapsRequest $request): DeleteExpiredSnapsResponse
    {
        $date = \DateTimeImmutable::createFromInterface($request->date);
        $date = $date->sub(new \DateInterval(Snap::EXPIRATION_INTERVAL));

        $snaps = $this->snapRepository->findExpiredSnaps($date);

        $ids = array_map(
            static fn (Snap $snap): Uuid => $snap->getId(),
            $snaps
        );

        $this->snapRepository->deleteByIds($ids);

        foreach ($snaps as $snap) {
            $this->fileStorage->delete($snap);
        }

        return new DeleteExpiredSnapsResponse($ids, count($ids));
    }
}
