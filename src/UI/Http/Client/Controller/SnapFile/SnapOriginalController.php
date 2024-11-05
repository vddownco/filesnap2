<?php

declare(strict_types=1);

namespace App\UI\Http\Client\Controller\SnapFile;

use App\Application\Domain\Snap\MimeType;
use App\Application\Domain\Snap\Snap;
use App\UI\Http\Client\Controller\AbstractSnapFileController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/snap/{id}',
    name: 'client_snap_file_original',
    methods: Request::METHOD_GET,
)]
final class SnapOriginalController extends AbstractSnapFileController
{
    protected function response(Snap $snap): BinaryFileResponse
    {
        return $this->file(
            $snap->getFile()->getAbsolutePath(),
            $snap->getOriginalFilename(),
            ResponseHeaderBag::DISPOSITION_INLINE
        );
    }

    protected function updateSnapLastSeenDate(): bool
    {
        return true;
    }

    protected function supportsMimeType(MimeType $mimeType): bool
    {
        return true;
    }
}
