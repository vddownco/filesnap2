<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\FindByUser;

use App\Application\Domain\Snap\Snap;
use App\Application\Domain\Snap\SnapRepositoryInterface;

final readonly class FindSnapsByUserUseCase
{
    public function __construct(
        private SnapRepositoryInterface $snapRepository,
    ) {
    }

    public function __invoke(FindSnapsByUserRequest $request): FindSnapsByUserResponse
    {
        $expirationCheckDate = null;

        if ($request->getExpirationCheckDate() !== null) {
            $expirationCheckDate = \DateTimeImmutable::createFromInterface($request->getExpirationCheckDate());
            $expirationCheckDate = $expirationCheckDate->sub(new \DateInterval(Snap::EXPIRATION_INTERVAL));
        }

        $snaps = $this->snapRepository->findByUser(
            $request->getUserId(),
            $request->getOffset(),
            $request->getLimit(),
            $expirationCheckDate
        );

        $totalCount = $this->snapRepository->countByUser($request->getUserId(), $expirationCheckDate);

        return new FindSnapsByUserResponse($snaps, $totalCount);
    }
}
