<?php

declare(strict_types=1);

namespace App\Tests\Domain\UseCase\Snap;

use App\Application\Domain\Snap\Exception\FileNotFoundException;
use App\Application\Domain\Snap\Exception\SnapNotFoundException;
use App\Application\Domain\Snap\FileStorage\File;
use App\Application\Domain\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Snap\MimeType;
use App\Application\Domain\Snap\SnapFactory;
use App\Application\Domain\Snap\SnapRepositoryInterface;
use App\Application\UseCase\Snap\DeleteById\DeleteSnapByIdRequest;
use App\Application\UseCase\Snap\DeleteById\DeleteSnapByIdUseCase;
use App\Tests\FilesnapTestCase;
use PHPUnit\Framework\MockObject\Exception;
use Random\RandomException;
use Symfony\Component\Uid\Uuid;

final class DeleteByIdTest extends FilesnapTestCase
{
    /**
     * @throws Exception
     */
    public function testItFailsSnapNotFound(): void
    {
        $request = new DeleteSnapByIdRequest(Uuid::v4());

        $fileStorageStub = self::createStub(FileStorageInterface::class);
        $snapRepositoryMock = $this->createMock(SnapRepositoryInterface::class);

        $snapRepositoryMock
            ->expects($this->once())
            ->method('findOneById')
            ->with($request->getId())
            ->willReturn(null);

        $useCase = new DeleteSnapByIdUseCase($snapRepositoryMock, $fileStorageStub);

        $this->expectException(SnapNotFoundException::class);

        $useCase($request);
    }

    /**
     * @throws Exception
     * @throws FileNotFoundException
     * @throws RandomException
     * @throws SnapNotFoundException
     */
    public function testItDeleteSnap(): void
    {
        $fileRepositoryMock = self::createConfiguredMock(FileStorageInterface::class, [
            'get' => new File('/this/is/an/absolute/path/to/file.jpg'),
        ]);

        $snapFactory = new SnapFactory($fileRepositoryMock);

        $snap = $snapFactory->create(
            id: Uuid::v4(),
            userId: Uuid::v7(),
            originalFilename: 'original-filename.jpg',
            mimeType: MimeType::ImageJpeg,
            creationDate: self::getRandomDateTime(),
            lastSeenDate: self::getRandomDateTime()
        );

        $snapRepositoryMock = $this->createMock(SnapRepositoryInterface::class);

        $snapRepositoryMock
            ->expects($this->once())
            ->method('findOneById')
            ->with($snap->getId())
            ->willReturn($snap);

        $snapRepositoryMock
            ->expects($this->once())
            ->method('deleteOneById')
            ->with($snap->getId());

        $fileRepositoryMock
            ->expects($this->once())
            ->method('delete')
            ->with($snap);

        $useCase = new DeleteSnapByIdUseCase($snapRepositoryMock, $fileRepositoryMock);
        $useCase(new DeleteSnapByIdRequest($snap->getId()));
    }
}
