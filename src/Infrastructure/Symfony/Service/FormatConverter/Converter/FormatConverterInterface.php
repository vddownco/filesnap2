<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter\Converter;

use App\Application\Domain\Snap\Snap;

interface FormatConverterInterface
{
    public function getFileAbsolutePath(Snap $snap): string;

    public function convert(Snap $snap): string;

    public function fileExists(Snap $snap): bool;

    public function deleteConvertedFile(Snap $snap): void;
}
