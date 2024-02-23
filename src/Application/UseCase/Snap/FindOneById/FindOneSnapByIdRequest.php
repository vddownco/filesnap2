<?php
declare(strict_types=1);

namespace App\Application\UseCase\Snap\FindOneById;

use Symfony\Component\Uid\Uuid;

final readonly class FindOneSnapByIdRequest
{
    public function __construct(private Uuid $id)
    {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
}