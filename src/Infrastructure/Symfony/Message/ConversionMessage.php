<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Message;

use App\Infrastructure\Symfony\Service\FormatConverter\Converter\ConvertFormat;
use Symfony\Component\Uid\Uuid;

final readonly class ConversionMessage
{
    public function __construct(
        private Uuid $snapId,
        private ConvertFormat $format
    ) {
    }

    public function getSnapId(): Uuid
    {
        return $this->snapId;
    }

    public function getFormat(): ConvertFormat
    {
        return $this->format;
    }
}
