<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\DeleteUserSnaps;

use App\Application\Domain\Snap\Exception\UnauthorizedDeletionException;
use App\Application\Domain\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Snap\Snap;
use App\Application\Domain\Snap\SnapRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final readonly class DeleteUserSnapsUseCase
{
    public function __construct(
        private SnapRepositoryInterface $snapRepository,
        private FileStorageInterface $fileStorage
    ) {
    }

    /**
     * @throws UnauthorizedDeletionException
     */
    public function __invoke(DeleteUserSnapsRequest $request): void
    {
        $snaps = $this->snapRepository->findByIds($request->getSnapIds());

        $unauthorizedToDeleteSnaps = array_filter(
            $snaps,
            static fn (Snap $snap): bool => $snap->getUserId()->toRfc4122() !== $request->getUserId()->toRfc4122()
        );

        if ($unauthorizedToDeleteSnaps !== []) {
            $ids = array_map(
                static fn (Snap $snap): Uuid => $snap->getId(),
                $unauthorizedToDeleteSnaps
            );

            throw new UnauthorizedDeletionException(...$ids);
        }

        $this->snapRepository->deleteByIds(
            $request->getUserId(),
            array_map(
                static fn (Snap $snap): Uuid => $snap->getId(),
                $snaps
            )
        );

        foreach ($snaps as $snap) {
            $this->fileStorage->delete($snap);
        }
    }
}
