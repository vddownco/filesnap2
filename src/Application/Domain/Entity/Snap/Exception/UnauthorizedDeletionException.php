<?php

declare(strict_types=1);

namespace App\Application\Domain\Entity\Snap\Exception;

use App\Application\Domain\Exception\DomainException;
use Symfony\Component\Uid\Uuid;

final class UnauthorizedDeletionException extends DomainException
{
    public function __construct(Uuid ...$uuids)
    {
        $uuidsRfc4122 = array_map(
            static fn (Uuid $uuid) => $uuid->toRfc4122(),
            $uuids
        );

        $uuidsList = implode(', ', $uuidsRfc4122);

        parent::__construct("Unauthorized deletion of snap(s) : $uuidsList.");
    }
}
