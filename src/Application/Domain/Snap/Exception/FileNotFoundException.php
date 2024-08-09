<?php

declare(strict_types=1);

namespace App\Application\Domain\Snap\Exception;

use App\Application\Domain\Exception\DomainException;
use Symfony\Component\Uid\Uuid;

final class FileNotFoundException extends DomainException
{
    public function __construct(Uuid $snapId)
    {
        parent::__construct(sprintf('No file found for snap id %s (%s).', $snapId->toBase58(), $snapId->toRfc4122()));
    }
}
