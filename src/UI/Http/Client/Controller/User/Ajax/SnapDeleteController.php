<?php

declare(strict_types=1);

namespace App\UI\Http\Client\Controller\User\Ajax;

use App\Application\Domain\Snap\Exception\UnauthorizedDeletionException;
use App\Application\UseCase\Snap\DeleteUserSnaps\DeleteUserSnapsRequest;
use App\Application\UseCase\Snap\DeleteUserSnaps\DeleteUserSnapsUseCase;
use App\Infrastructure\Symfony\Attribute\MapPayloadUuidsFromBase58;
use App\UI\Http\FilesnapAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route(
    path: '/u/snap/delete',
    name: 'client_user_ajax_snap_delete',
    methods: Request::METHOD_POST,
    format: 'json'
)]
final class SnapDeleteController extends FilesnapAbstractController
{
    /**
     * @param list<Uuid> $ids
     *
     * @throws UnauthorizedDeletionException
     */
    public function __invoke(
        DeleteUserSnapsUseCase $deleteUserSnapsUseCase,
        Request $request,
        #[MapPayloadUuidsFromBase58] array $ids,
    ): Response {
        ($deleteUserSnapsUseCase)(new DeleteUserSnapsRequest(
            $this->getAuthenticatedUser()->getId(),
            $ids
        ));

        return $this->emptyResponse();
    }
}
