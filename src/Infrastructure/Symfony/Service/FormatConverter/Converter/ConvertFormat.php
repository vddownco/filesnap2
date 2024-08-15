<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter\Converter;

enum ConvertFormat: string
{
    case Webm = 'webm';
    case Webp = 'webp';
}
