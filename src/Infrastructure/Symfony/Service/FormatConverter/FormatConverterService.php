<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter;

use App\Application\Domain\Snap\Snap;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\FormatStorageInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class FormatConverterService
{
    public function __construct(
        /** @var list<FormatStorageInterface> $formatStorageImplementations */
        #[AutowireIterator(tag: 'format.storage.interface')] private iterable $formatStorageImplementations,
    ) {
    }

    public function deleteConvertedFiles(Snap $snap): void
    {
        foreach ($this->formatStorageImplementations as $formatStorageImplementation) {
            $formatStorageImplementation->delete($snap);
        }
    }
}
