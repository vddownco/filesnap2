<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\DeleteUserSnaps;

use App\Application\Domain\Exception\InvalidRequestParameterException;
use App\Application\Domain\Snap\Exception\UnauthorizedDeletionException;
use App\Application\Domain\Snap\Exception\UnknownSnapsException;
use App\Application\Domain\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Snap\Snap;
use App\Application\Domain\Snap\SnapRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final readonly class DeleteUserSnapsUseCase
{
    public function __construct(
        private SnapRepositoryInterface $snapRepository,
        private FileStorageInterface $fileStorage,
    ) {
    }

    /**
     * @throws UnauthorizedDeletionException
     * @throws InvalidRequestParameterException
     * @throws UnknownSnapsException
     */
    public function __invoke(DeleteUserSnapsRequest $request): void
    {
        $requestSnapIds = $request->getSnapIds();

        if ($requestSnapIds === []) {
            throw new InvalidRequestParameterException('SnapIds', 'Empty list');
        }

        $foundSnaps = $this->snapRepository->findByIds($requestSnapIds);

        $foundSnapsIdsRfc = array_map(
            static fn (Snap $snap): string => $snap->getId()->toRfc4122(),
            $foundSnaps
        );

        $notFoundSnaps = array_filter(
            $requestSnapIds,
            static fn (Uuid $uuid): bool => in_array($uuid->toRfc4122(), $foundSnapsIdsRfc, true) === false
        );

        if ($notFoundSnaps !== []) {
            throw new UnknownSnapsException(...$notFoundSnaps);
        }

        $unauthorizedToDeleteSnaps = array_filter(
            $foundSnaps,
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
                $foundSnaps
            )
        );

        foreach ($foundSnaps as $foundSnap) {
            $this->fileStorage->delete($foundSnap);
        }
    }
}
