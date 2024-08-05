<?php

declare(strict_types=1);

namespace App\UI\Http\Client\Controller;

use App\Application\Domain\Entity\Snap\MimeType;
use App\Application\Domain\Entity\Snap\Snap;
use App\Application\UseCase\Snap\FindOneById\FindOneSnapByIdRequest;
use App\Application\UseCase\Snap\FindOneById\FindOneSnapByIdUseCase;
use App\Application\UseCase\Snap\UpdateLastSeenDate\UpdateSnapLastSeenDateRequest;
use App\Application\UseCase\Snap\UpdateLastSeenDate\UpdateSnapLastSeenDateUseCase;
use App\Infrastructure\Symfony\Attribute\MapUuidFromBase58;
use App\UI\Http\FilesnapAbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

abstract class AbstractSnapFileController extends FilesnapAbstractController
{
    public function __invoke(
        FindOneSnapByIdUseCase $findOneSnapByIdUseCase,
        UpdateSnapLastSeenDateUseCase $updateSnapLastSeenDateUseCase,
        #[MapUuidFromBase58] Uuid $id
    ): Response {
        $useCaseResponse = $findOneSnapByIdUseCase(new FindOneSnapByIdRequest($id));
        $snap = $useCaseResponse->getSnap();

        if (
            $snap === null
            || in_array($snap->getMimeType(), $this->supportedMimeTypes(), true) === false
        ) {
            throw $this->createNotFoundException();
        }

        $response = $this->response($snap);

        $response->headers->set('X-Robots-Tag', 'noindex');
        $response->headers->set('Cache-Control', 'no-store');

        if ($this->updateSnapLastSeenDate() === true) {
            $updateSnapLastSeenDateUseCase(new UpdateSnapLastSeenDateRequest($snap->getId(), new \DateTime()));
        }

        return $response;
    }

    abstract protected function response(Snap $snap): Response;

    abstract protected function updateSnapLastSeenDate(): bool;

    /**
     * @return MimeType[]
     */
    abstract protected function supportedMimeTypes(): array;
}
