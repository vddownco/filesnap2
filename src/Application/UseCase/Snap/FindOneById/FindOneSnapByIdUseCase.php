<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\FindOneById;

use App\Application\Domain\Entity\Snap\Repository\SnapRepositoryInterface;

final readonly class FindOneSnapByIdUseCase
{
    public function __construct(private SnapRepositoryInterface $snapRepository)
    {
    }

    public function __invoke(FindOneSnapByIdRequest $request): FindOneSnapByIdResponse
    {
        return new FindOneSnapByIdResponse($this->snapRepository->findOneById($request->getId()));
    }
}
