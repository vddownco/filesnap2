<?php

declare(strict_types=1);

namespace App\Application\Domain\User\Exception;

use App\Application\Domain\Exception\DomainException;

final class AlreadyExistingUserWithEmail extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('User already existing with email %s.', $email));
    }
}
