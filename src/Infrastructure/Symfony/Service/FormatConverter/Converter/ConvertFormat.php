<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter\Converter;

enum ConvertFormat: string
{
    case Thumbnail = 'thumbnail';
    case Webm = 'webm';
    case Webp = 'webp';
}
