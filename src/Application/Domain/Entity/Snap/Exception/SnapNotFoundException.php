<?php

declare(strict_types=1);

namespace App\Application\Domain\Entity\Snap\Exception;

use App\Application\Domain\Exception\DomainException;
use Symfony\Component\Uid\Uuid;

final class SnapNotFoundException extends DomainException
{
    public function __construct(Uuid $id)
    {
        parent::__construct(
            "Snap not found for id {$id->toRfc4122()} ({$id->toBase58()})."
        );
    }
}
