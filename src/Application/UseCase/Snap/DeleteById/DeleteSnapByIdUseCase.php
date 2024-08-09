<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\DeleteById;

use App\Application\Domain\Snap\Exception\SnapNotFoundException;
use App\Application\Domain\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Snap\SnapRepositoryInterface;

final readonly class DeleteSnapByIdUseCase
{
    public function __construct(
        private SnapRepositoryInterface $snapRepository,
        private FileStorageInterface $fileStorage
    ) {
    }

    /**
     * @throws SnapNotFoundException
     */
    public function __invoke(DeleteSnapByIdRequest $request): void
    {
        $snap = $this->snapRepository->findOneById($request->getId());

        if ($snap === null) {
            throw new SnapNotFoundException($request->getId());
        }

        $this->snapRepository->deleteOneById($snap->getId());
        $this->fileStorage->delete($snap);
    }
}
