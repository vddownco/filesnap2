<?php

declare(strict_types=1);

namespace App\Application\Domain\Snap\Exception;

use App\Application\Domain\Exception\DomainException;
use Symfony\Component\Uid\Uuid;

final class SnapNotFoundException extends DomainException
{
    public function __construct(Uuid $id)
    {
        parent::__construct(sprintf('Snap not found for id %s (%s).', $id->toBase58(), $id->toRfc4122()));
    }
}
