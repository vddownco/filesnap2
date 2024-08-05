<?php

declare(strict_types=1);

namespace App\UI\Http\Client\Controller\SnapFile;

use App\Application\Domain\Entity\Snap\MimeType;
use App\Application\Domain\Entity\Snap\Snap;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\WebpConverter;
use App\UI\Http\Client\Controller\AbstractSnapFileController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/snap/{id}.webp',
    name: 'client_snap_file_webp',
    methods: Request::METHOD_GET,
    priority: 1,
    stateless: true
)]
final class SnapWebpController extends AbstractSnapFileController
{
    /**
     * @throws \Exception
     */
    protected function response(Snap $snap): BinaryFileResponse
    {
        $webpConverter = new WebpConverter();

        if ($webpConverter->fileExists($snap) === false) {
            $webpConverter->convert($snap);
        }

        return $this->file(
            $webpConverter->getFileAbsolutePath($snap),
            $snap->getOriginalFilename() . '.webp',
            ResponseHeaderBag::DISPOSITION_INLINE
        );
    }

    protected function updateSnapLastSeenDate(): bool
    {
        return true;
    }

    /**
     * @return MimeType[]
     */
    protected function supportedMimeTypes(): array
    {
        return MimeType::IMAGES_MIME_TYPES;
    }
}
