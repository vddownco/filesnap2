<?php
declare(strict_types=1);

namespace App\Application\Domain\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class DomainEntity
{
}