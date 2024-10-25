<?php

declare(strict_types=1);

namespace App\UI\Http\Client\Controller\SnapFile;

use App\Application\Domain\Snap\MimeType;
use App\Application\Domain\Snap\Snap;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\ConvertFormat;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\Webm\WebmConverter;
use App\UI\Http\Client\Controller\AbstractSnapFileController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Very poor performance at the moment.
 */
#[Route(
    path: '/snap/{id}.webm',
    name: 'client_snap_file_webm',
    methods: Request::METHOD_GET,
    priority: 1,
)]
final class SnapWebmController extends AbstractSnapFileController
{
    public function __construct(
        private readonly WebmConverter $converter,
    ) {
    }

    /**
     * @throws \Exception
     */
    protected function response(Snap $snap): Response
    {
        $webmFile = $this->converter->getConvertedFile($snap);

        if ($webmFile === null) {
            return $this->waitingForConversionResponse($snap, ConvertFormat::Webm);
        }

        return $this->file(
            $webmFile,
            sprintf('%s.webm', $snap->getOriginalFilename()),
            ResponseHeaderBag::DISPOSITION_INLINE
        );
    }

    protected function updateSnapLastSeenDate(): bool
    {
        return true;
    }

    /**
     * @return list<MimeType>
     */
    protected function supportedMimeTypes(): array
    {
        return MimeType::VIDEO_MIME_TYPES;
    }
}
