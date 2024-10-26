<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter;

use App\Application\Domain\Snap\Snap;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\Avif\AvifLocalStorage;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\Thumbnail\ThumbnailLocalStorage;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\Webm\WebmLocalStorage;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\Webp\WebpLocalStorage;

final readonly class FormatConverterService
{
    public function __construct(
        private ThumbnailLocalStorage $thumbnailLocalStorage,
        private WebpLocalStorage $webpLocalStorage,
        private WebmLocalStorage $webmLocalStorage,
        private AvifLocalStorage $avifLocalStorage,
    ) {
    }

    public function deleteConvertedFiles(Snap $snap): void
    {
        $this->thumbnailLocalStorage->delete($snap);

        if ($snap->isImage() === true) {
            $this->webpLocalStorage->delete($snap);
            $this->avifLocalStorage->delete($snap);
        }

        if ($snap->isVideo() === true) {
            $this->webmLocalStorage->delete($snap);
        }
    }
}
