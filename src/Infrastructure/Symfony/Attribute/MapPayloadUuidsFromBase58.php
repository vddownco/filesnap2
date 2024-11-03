<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Attribute;

use App\Infrastructure\Symfony\ArgumentResolver\PayloadUuidsFromBase58ValueResolver;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class MapPayloadUuidsFromBase58 extends ValueResolver
{
    public function __construct()
    {
        parent::__construct(PayloadUuidsFromBase58ValueResolver::class);
    }
}
