<?php

declare(strict_types=1);

namespace App\UI\Http\Client\Controller\User\Ajax;

use App\Application\Domain\Entity\Snap\Exception\SnapNotFoundException;
use App\Application\UseCase\Snap\DeleteById\DeleteSnapByIdRequest;
use App\Application\UseCase\Snap\DeleteById\DeleteSnapByIdUseCase;
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
     * @throws SnapNotFoundException
     */
    public function __invoke(
        DeleteSnapByIdUseCase $deleteSnapByIdUseCase,
        Request $request
    ): Response {
        $deleteRequests = array_map(
            static fn (string $id) => new DeleteSnapByIdRequest(Uuid::fromString($id)),
            $request->getPayload()->all('ids')
        );

        foreach ($deleteRequests as $deleteRequest) {
            $deleteSnapByIdUseCase($deleteRequest);
        }

        return $this->emptyResponse();
    }
}
