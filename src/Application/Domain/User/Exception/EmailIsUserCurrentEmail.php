<?php

declare(strict_types=1);

namespace App\Application\Domain\User\Exception;

use App\Application\Domain\Exception\DomainException;

final class EmailIsUserCurrentEmail extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('Email %s is user current email.', $email));
    }
}
