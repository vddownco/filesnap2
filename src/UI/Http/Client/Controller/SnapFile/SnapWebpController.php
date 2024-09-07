<?php

declare(strict_types=1);

namespace App\UI\Http\Client\Controller\SnapFile;

use App\Application\Domain\Snap\MimeType;
use App\Application\Domain\Snap\Snap;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\ConvertFormat;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\Webp\WebpConverter;
use App\UI\Http\Client\Controller\AbstractSnapFileController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/snap/{id}.webp',
    name: 'client_snap_file_webp',
    methods: Request::METHOD_GET,
    priority: 1,
)]
final class SnapWebpController extends AbstractSnapFileController
{
    public function __construct(
        private readonly WebpConverter $converter,
    ) {
    }

    /**
     * @throws \Exception
     */
    protected function response(Snap $snap): Response
    {
        $webpFile = $this->converter->getConvertedFile($snap);

        if ($webpFile === null) {
            return $this->waitingForConversionResponse($snap, ConvertFormat::Webp);
        }

        return $this->file(
            $webpFile,
            $snap->getOriginalFilename() . '.webp',
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
        return MimeType::IMAGE_MIME_TYPES;
    }
}
