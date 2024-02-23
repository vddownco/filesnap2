<?php
declare(strict_types=1);

namespace App\Application\Domain\Entity\Snap\Exception;

use App\Application\Domain\Exception\DomainException;

final class UnsupportedFileTypeException extends DomainException
{
    public function __construct(string $mimeType)
    {
        parent::__construct("The mimetype '$mimeType' is not supported.");
    }
}