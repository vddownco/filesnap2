<?php
declare(strict_types=1);

namespace App\Application\Domain\Entity\Snap\Exception;

use App\Application\Domain\Exception\DomainException;
use Symfony\Component\Uid\Uuid;

final class FileNotFoundException extends DomainException
{
    public function __construct(Uuid $snapId)
    {
        parent::__construct(
            "No file found for snap id {$snapId->toBase58()} ({$snapId->toRfc4122()})."
        );
    }
}