<?php

declare(strict_types=1);

namespace App\UI\Http\Client\Controller;

use App\Application\UseCase\Snap\FindOneById\FindOneSnapByIdRequest;
use App\Application\UseCase\Snap\FindOneById\FindOneSnapByIdUseCase;
use App\Application\UseCase\Snap\UpdateLastSeenDate\UpdateSnapLastSeenDateRequest;
use App\Application\UseCase\Snap\UpdateLastSeenDate\UpdateSnapLastSeenDateUseCase;
use App\Infrastructure\Symfony\Attribute\MapUuidFromBase58;
use App\UI\Http\FilesnapAbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route(
    path: '/snap/{id}',
    name: 'client_snap_file',
    methods: Request::METHOD_GET,
    stateless: true
)]
final class SnapFileController extends FilesnapAbstractController
{
    public function __invoke(
        FindOneSnapByIdUseCase $findOneSnapByIdUseCase,
        UpdateSnapLastSeenDateUseCase $updateSnapLastSeenDateUseCase,
        #[MapUuidFromBase58] Uuid $id
    ): BinaryFileResponse {
        $useCaseResponse = $findOneSnapByIdUseCase(new FindOneSnapByIdRequest($id));
        $snap = $useCaseResponse->getSnap();

        if ($snap === null) {
            throw $this->createNotFoundException();
        }

        $updateSnapLastSeenDateUseCase(new UpdateSnapLastSeenDateRequest($snap->getId(), new \DateTime()));

        $binaryFileResponse = $this->file(
            $snap->getFile()->getAbsolutePath(),
            $snap->getOriginalFilename(),
            ResponseHeaderBag::DISPOSITION_INLINE
        );

        $binaryFileResponse->headers->set('X-Robots-Tag', 'noindex');
        $binaryFileResponse->headers->set('Cache-Control', 'no-store');

        return $binaryFileResponse;
    }
}
