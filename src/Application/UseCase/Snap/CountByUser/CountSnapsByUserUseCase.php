<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\CountByUser;

use App\Application\Domain\Entity\Snap\Repository\SnapRepositoryInterface;

final readonly class CountSnapsByUserUseCase
{
    public function __construct(private SnapRepositoryInterface $snapRepository)
    {
    }

    public function __invoke(CountSnapsByUserRequest $request): CountSnapsByUserResponse
    {
        return new CountSnapsByUserResponse(
            $this->snapRepository->countByUser($request->getUserId())
        );
    }
}
