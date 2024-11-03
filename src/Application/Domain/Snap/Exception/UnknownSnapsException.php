<?php

declare(strict_types=1);

namespace App\Application\Domain\Snap\Exception;

use App\Application\Domain\Exception\DomainException;
use Symfony\Component\Uid\Uuid;

final class UnknownSnapsException extends DomainException
{
    public function __construct(Uuid ...$uuids)
    {
        $uuidsRfc4122 = array_map(
            static fn (Uuid $uuid) => $uuid->toRfc4122(),
            $uuids
        );

        parent::__construct(sprintf('Unknown snap(s) : %s', implode(', ', $uuidsRfc4122)));
    }
}
