<?php

declare(strict_types=1);

namespace App\UI\Http\Api\Controller;

use App\Application\Domain\Snap\Exception\UnauthorizedDeletionException;
use App\Application\UseCase\Snap\DeleteUserSnaps\DeleteUserSnapsRequest;
use App\Application\UseCase\Snap\DeleteUserSnaps\DeleteUserSnapsUseCase;
use App\Application\UseCase\Snap\FindOneById\FindOneSnapByIdRequest;
use App\Application\UseCase\Snap\FindOneById\FindOneSnapByIdUseCase;
use App\Infrastructure\Symfony\Attribute\MapUuidFromBase58;
use App\UI\Http\FilesnapAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route(
    path: '/api/snap/{id}',
    name: 'api_snap_delete',
    methods: Request::METHOD_DELETE,
    format: 'json'
)]
final class SnapDeleteController extends FilesnapAbstractController
{
    /**
     * @throws UnauthorizedDeletionException
     */
    public function __invoke(
        FindOneSnapByIdUseCase $findOneSnapByIdUseCase,
        DeleteUserSnapsUseCase $deleteUserSnapsUseCase,
        Request $request,
        #[MapUuidFromBase58] Uuid $id,
    ): Response {
        $useCaseResponse = $findOneSnapByIdUseCase(new FindOneSnapByIdRequest($id));
        $snap = $useCaseResponse->getSnap();

        if ($snap === null) {
            throw $this->createNotFoundException();
        }

        $deleteUserSnapsUseCase(new DeleteUserSnapsRequest(
            $this->getAuthenticatedUser()->getId(),
            [$snap->getId()]
        ));

        return $this->emptyResponse();
    }
}
