<?php
declare(strict_types=1);

namespace App\Application\UseCase\Snap\FindByUser;

use App\Application\Domain\Entity\Snap\Repository\SnapRepositoryInterface;

final readonly class FindSnapsByUserUseCase
{
    public function __construct(private SnapRepositoryInterface $snapRepository)
    {
    }

    public function __invoke(FindSnapsByUserRequest $request): FindSnapsByUserResponse
    {
        return new FindSnapsByUserResponse(
            $this->snapRepository->findByUser(
                $request->getUserId(),
                $request->getOffset(),
                $request->getLimit()
            )
        );
    }
}