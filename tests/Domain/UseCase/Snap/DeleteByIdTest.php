<?php

declare(strict_types=1);

namespace App\Tests\Domain\UseCase\Snap;

use App\Application\Domain\Entity\Snap\Exception\SnapNotFoundException;
use App\Application\Domain\Entity\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Entity\Snap\Repository\SnapRepositoryInterface;
use App\Application\UseCase\Snap\DeleteById\DeleteSnapByIdRequest;
use App\Application\UseCase\Snap\DeleteById\DeleteSnapByIdUseCase;
use App\Tests\FilesnapTestCase;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Uid\Uuid;

final class DeleteByIdTest extends FilesnapTestCase
{
    /**
     * @throws Exception
     */
    public function testItFailsSnapNotFound(): void
    {
        $fileStorageStub = $this->createStub(FileStorageInterface::class);
        $snapRepositoryStub = $this->createConfiguredStub(SnapRepositoryInterface::class, [
            'findOneById' => null,
        ]);

        $useCase = new DeleteSnapByIdUseCase($snapRepositoryStub, $fileStorageStub);

        $this->expectException(SnapNotFoundException::class);

        $useCase(new DeleteSnapByIdRequest(Uuid::v4()));
    }
}
