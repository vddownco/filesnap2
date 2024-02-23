<?php
declare(strict_types=1);

namespace App\Application\UseCase\Snap\DeleteById;

use App\Application\Domain\Entity\Snap\Exception\SnapNotFoundException;
use App\Application\Domain\Entity\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Entity\Snap\Repository\SnapRepositoryInterface;

final readonly class DeleteSnapByIdUseCase
{
    public function __construct(
        private SnapRepositoryInterface $snapRepository,
        private FileStorageInterface $fileStorage
    )
    {
    }

    /**
     * @throws SnapNotFoundException
     */
    public function __invoke(DeleteSnapByIdRequest $request): void
    {
        $snap = $this->snapRepository->findOneById($request->getId());

        if (null === $snap) {
            throw new SnapNotFoundException($request->getId());
        }

        $this->fileStorage->delete($snap->getId(), $snap->getUserId());
        $this->snapRepository->deleteOneById($snap->getId());
    }
}