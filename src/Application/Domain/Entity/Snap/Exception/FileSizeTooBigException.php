<?php

declare(strict_types=1);

namespace App\Application\Domain\Entity\Snap\Exception;

use App\Application\Domain\Exception\DomainException;

final class FileSizeTooBigException extends DomainException
{
    public function __construct(int $fileMaximumAuthorizedBytesSize)
    {
        $maxSizeMb = round(
            $fileMaximumAuthorizedBytesSize * 0.000001,
            2,
            PHP_ROUND_HALF_DOWN
        );

        parent::__construct(
            "The maximum authorized file size is $maxSizeMb MB."
        );
    }
}
