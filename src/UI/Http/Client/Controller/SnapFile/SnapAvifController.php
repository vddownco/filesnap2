<?php

declare(strict_types=1);

namespace App\UI\Http\Client\Controller\SnapFile;

use App\Application\Domain\Snap\MimeType;
use App\Application\Domain\Snap\Snap;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\Avif\AvifConverter;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\ConvertFormat;
use App\UI\Http\Client\Controller\AbstractSnapFileController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/snap/{id}.avif',
    name: 'client_snap_file_avif',
    methods: Request::METHOD_GET,
    priority: 1,
)]
final class SnapAvifController extends AbstractSnapFileController
{
    public function __construct(
        private readonly AvifConverter $converter,
    ) {
    }

    /**
     * @throws \Exception
     */
    protected function response(Snap $snap): Response
    {
        $avifFile = $this->converter->getConvertedFile($snap);

        if ($avifFile === null) {
            return $this->waitingForConversionResponse($snap, ConvertFormat::Avif);
        }

        return $this->file(
            $avifFile,
            $snap->getOriginalFilename() . '.avif',
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
