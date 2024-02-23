<?php
declare(strict_types=1);

namespace App\UI\Http\Client\Controller;

use App\Application\UseCase\Snap\FindOneById\FindOneSnapByIdRequest;
use App\Application\UseCase\Snap\FindOneById\FindOneSnapByIdUseCase;
use App\Infrastructure\Symfony\Attribute\MapUuidFromBase58;
use App\Infrastructure\Symfony\Service\ThumbnailService;
use App\UI\Http\FilesnapAbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

/**
 * This route is triggered only if the .thumbnail file for a Snap doesn't exist in public/snap
 * It generate the thumbnail and redirect to itself to let the server return the previously generated thumbnail file
 */
#[Route(
    path: '/snap/{id}.thumbnail',
    name: 'client_snap_thumbnail',
    methods: Request::METHOD_GET,
    priority: 1,
    stateless: true
)]
final class SnapThumbnailController extends FilesnapAbstractController
{
    public function __invoke(
        FindOneSnapByIdUseCase $findOneSnapByIdUseCase,
        ThumbnailService $thumbnailService,
        #[MapUuidFromBase58] Uuid $id
    ): RedirectResponse
    {
        $useCaseResponse = $findOneSnapByIdUseCase(new FindOneSnapByIdRequest($id));
        $snap = $useCaseResponse->getSnap();

        if (null === $snap) {
            throw $this->createNotFoundException();
        }

        $thumbnailService->generate($snap);

        return $this->redirectToRoute('client_snap_thumbnail', ['id' => $id]);
    }
}
