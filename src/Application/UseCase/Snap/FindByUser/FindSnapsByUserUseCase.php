<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\FindByUser;

use App\Application\Domain\Snap\SnapRepositoryInterface;

final readonly class FindSnapsByUserUseCase
{
    public function __construct(
        private SnapRepositoryInterface $snapRepository,
    ) {
    }

    public function __invoke(FindSnapsByUserRequest $request): FindSnapsByUserResponse
    {
        $snaps = $this->snapRepository->findByUser(
            $request->getUserId(),
            $request->getOffset(),
            $request->getLimit()
        );

        $totalCount = $this->snapRepository->countByUser($request->getUserId());

        return new FindSnapsByUserResponse($snaps, $totalCount);
    }
}
