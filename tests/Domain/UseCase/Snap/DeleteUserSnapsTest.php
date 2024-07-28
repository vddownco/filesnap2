<?php

declare(strict_types=1);

namespace App\Tests\Domain\UseCase\Snap;

use App\Application\Domain\Entity\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Entity\Snap\Repository\SnapRepositoryInterface;
use App\Application\UseCase\Snap\DeleteUserSnaps\DeleteUserSnapsRequest;
use App\Application\UseCase\Snap\DeleteUserSnaps\DeleteUserSnapsUseCase;
use App\Tests\FilesnapTestCase;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Uid\Uuid;

final class DeleteUserSnapsTest extends FilesnapTestCase
{
    /**
     * @throws Exception
     */
    public function testItDeletesUserSnaps(): void
    {
        $userId = Uuid::v7();
        $snapIds = [];

        for ($i = 0; $i < 10; ++$i) {
            $snapIds[] = Uuid::v4();
        }

        $snapRepositoryMock = $this->createMock(SnapRepositoryInterface::class);
        $fileRepositoryMock = $this->createMock(FileStorageInterface::class);

        $snapRepositoryMock
            ->expects($this->once())
            ->method('deleteByIds')
            ->with($userId, $snapIds);

        $fileRepositoryMock
            ->expects($this->exactly(count($snapIds)))
            ->method('delete');

        $useCase = new DeleteUserSnapsUseCase($snapRepositoryMock, $fileRepositoryMock);
        $useCase(new DeleteUserSnapsRequest($userId, $snapIds));
    }
}
