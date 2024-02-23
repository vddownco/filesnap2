<?php
declare(strict_types=1);

namespace App\Application\UseCase\Snap\DeleteById;

use Symfony\Component\Uid\Uuid;

final readonly class DeleteSnapByIdRequest
{
    public function __construct(private Uuid $id)
    {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
}