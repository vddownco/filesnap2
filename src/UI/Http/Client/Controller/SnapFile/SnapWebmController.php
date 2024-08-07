<?php

declare(strict_types=1);

namespace App\UI\Http\Client\Controller\SnapFile;

use App\Application\Domain\Entity\Snap\MimeType;
use App\Application\Domain\Entity\Snap\Snap;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\WebmConverter;
use App\UI\Http\Client\Controller\AbstractSnapFileController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
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
    stateless: true
)]
final class SnapWebmController extends AbstractSnapFileController
{
    /**
     * @throws \Exception
     */
    protected function response(Snap $snap): BinaryFileResponse
    {
        $webmConverter = new WebmConverter();

        if ($webmConverter->fileExists($snap) === false) {
            $webmConverter->convert($snap);
        }

        return $this->file(
            $webmConverter->getFileAbsolutePath($snap),
            $snap->getOriginalFilename() . '.webm',
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
