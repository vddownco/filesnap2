<?php

declare(strict_types=1);

namespace App\Application\UseCase\User\UpdateAuthorizationKeyById;

use Symfony\Component\Uid\Uuid;

final readonly class UpdateUserAuthorizationKeyByIdResponse
{
    public function __construct(
        private Uuid $authorizationKey
    ) {
    }

    public function getAuthorizationKey(): Uuid
    {
        return $this->authorizationKey;
    }
}
