<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\UpdateLastSeenDate;

use App\Application\Domain\Snap\SnapRepositoryInterface;

final readonly class UpdateSnapLastSeenDateUseCase
{
    public function __construct(
        private SnapRepositoryInterface $snapRepository
    ) {
    }

    public function __invoke(UpdateSnapLastSeenDateRequest $request): void
    {
        $this->snapRepository->updateLastSeenDate(
            $request->getId(),
            $request->getLastSeenDate()
        );
    }
}
