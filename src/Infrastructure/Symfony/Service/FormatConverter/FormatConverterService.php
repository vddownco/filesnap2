<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter;

use App\Application\Domain\Snap\Snap;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\Webm\WebmLocalStorage;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\Webp\WebpLocalStorage;

final readonly class FormatConverterService
{
    public function __construct(
        private WebpLocalStorage $webpLocalStorage,
        private WebmLocalStorage $webmLocalStorage,
    ) {
    }

    public function deleteConvertedFiles(Snap $snap): void
    {
        if ($snap->isImage() === true) {
            $this->webpLocalStorage->delete($snap);
        }

        if ($snap->isVideo() === true) {
            $this->webmLocalStorage->delete($snap);
        }
    }
}
