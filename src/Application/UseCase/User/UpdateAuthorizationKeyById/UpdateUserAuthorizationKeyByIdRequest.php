<?php
declare(strict_types=1);

namespace App\Application\UseCase\User\UpdateAuthorizationKeyById;

use Symfony\Component\Uid\Uuid;

final readonly class UpdateUserAuthorizationKeyByIdRequest
{
    public function __construct(private Uuid $id)
    {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
}