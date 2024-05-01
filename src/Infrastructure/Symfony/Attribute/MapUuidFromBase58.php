<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Attribute;

use App\Infrastructure\Symfony\ArgumentResolver\UuidFromBase58ValueResolver;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class MapUuidFromBase58 extends ValueResolver
{
    public function __construct(public ?string $name = null)
    {
        parent::__construct(UuidFromBase58ValueResolver::class);
    }
}
