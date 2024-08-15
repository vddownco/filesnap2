<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter\Converter;

use App\Application\Domain\Snap\Snap;
use Symfony\Component\HttpFoundation\File\File;

abstract readonly class AbstractConverter
{
    public function __construct(
        private FormatStorageInterface $formatStorage
    ) {
    }

    public function convert(Snap $snap): void
    {
        $convertedFile = $this->conversion($snap);
        $this->formatStorage->save($snap, $convertedFile);
        $this->cleanUp($snap);
    }

    public function getConvertedFile(Snap $snap): ?File
    {
        return $this->formatStorage->get($snap);
    }

    abstract public function isConversionInProgress(Snap $snap): bool;

    abstract protected function conversion(Snap $snap): File;

    abstract protected function cleanUp(Snap $snap): void;
}
