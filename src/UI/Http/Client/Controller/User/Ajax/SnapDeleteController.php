<?php

declare(strict_types=1);

namespace App\UI\Http\Client\Controller\User\Ajax;

use App\Application\Domain\Snap\Exception\UnauthorizedDeletionException;
use App\Application\UseCase\Snap\DeleteUserSnaps\DeleteUserSnapsRequest;
use App\Application\UseCase\Snap\DeleteUserSnaps\DeleteUserSnapsUseCase;
use App\UI\Http\FilesnapAbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
     * @throws UnauthorizedDeletionException
     */
    public function __invoke(
        DeleteUserSnapsUseCase $deleteUserSnapsUseCase,
        Request $request
    ): Response {
        $payloadIds = $request->getPayload()->all('ids');

        $stringIds = array_filter(
            $payloadIds,
            static fn (mixed $id): bool => is_string($id) === true
        );

        if (count($payloadIds) !== count($stringIds)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST);
        }

        $snapIds = array_map(
            static fn (string $id) => Uuid::fromString($id),
            $stringIds
        );

        ($deleteUserSnapsUseCase)(new DeleteUserSnapsRequest(
            $this->getAuthenticatedUser()->getId(),
            $snapIds
        ));

        return $this->emptyResponse();
    }
}
