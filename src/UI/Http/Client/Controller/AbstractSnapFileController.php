<?php

declare(strict_types=1);

namespace App\UI\Http\Client\Controller;

use App\Application\Domain\Snap\MimeType;
use App\Application\Domain\Snap\Snap;
use App\Application\UseCase\Snap\FindOneById\FindOneSnapByIdRequest;
use App\Application\UseCase\Snap\FindOneById\FindOneSnapByIdUseCase;
use App\Application\UseCase\Snap\UpdateLastSeenDate\UpdateSnapLastSeenDateRequest;
use App\Application\UseCase\Snap\UpdateLastSeenDate\UpdateSnapLastSeenDateUseCase;
use App\Infrastructure\Symfony\Attribute\MapUuidFromBase58;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\ConvertFormat;
use App\UI\Http\FilesnapAbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

abstract class AbstractSnapFileController extends FilesnapAbstractController
{
    public function __invoke(
        FindOneSnapByIdUseCase $findOneSnapByIdUseCase,
        UpdateSnapLastSeenDateUseCase $updateSnapLastSeenDateUseCase,
        #[MapUuidFromBase58] Uuid $id,
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

        if ($this->updateSnapLastSeenDate() === true && $response instanceof BinaryFileResponse) {
            $updateSnapLastSeenDateUseCase(new UpdateSnapLastSeenDateRequest($snap->getId(), new \DateTimeImmutable()));
        }

        return $response;
    }

    protected function waitingForConversionResponse(Snap $snap, ConvertFormat $format): Response
    {
        return $this->render('client/waiting-for-conversion.html.twig', [
            'snap' => $snap,
            'format' => $format,
        ]);
    }

    abstract protected function response(Snap $snap): Response;

    abstract protected function updateSnapLastSeenDate(): bool;

    /**
     * @return list<MimeType>
     */
    abstract protected function supportedMimeTypes(): array;
}
