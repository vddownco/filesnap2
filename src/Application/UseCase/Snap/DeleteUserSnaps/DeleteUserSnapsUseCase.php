<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\DeleteUserSnaps;

use App\Application\Domain\Entity\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Entity\Snap\Repository\SnapRepositoryInterface;

final readonly class DeleteUserSnapsUseCase
{
    public function __construct(
        private SnapRepositoryInterface $snapRepository,
        private FileStorageInterface $fileStorage
    ) {
    }

    public function __invoke(DeleteUserSnapsRequest $request): void
    {
        $this->snapRepository->deleteByIds($request->getUserId(), $request->getSnapIds());

        foreach ($request->getSnapIds() as $snapId) {
            $this->fileStorage->delete($snapId, $request->getUserId());
        }
    }
}
