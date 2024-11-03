<?php

declare(strict_types=1);

namespace App\Application\Domain\Exception;

final class InvalidRequestParameterException extends DomainException
{
    public function __construct(string $parameterName, string $message)
    {
        parent::__construct(sprintf('Invalid request parameter (%s): %s', $parameterName, $message));
    }
}
