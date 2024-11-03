<?php

declare(strict_types=1);

namespace App\Tests\Domain\UseCase\Snap;

use App\Application\Domain\Exception\InvalidRequestParameterException;
use App\Application\Domain\Snap\Exception\FileNotFoundException;
use App\Application\Domain\Snap\Exception\UnauthorizedDeletionException;
use App\Application\Domain\Snap\Exception\UnknownSnapsException;
use App\Application\Domain\Snap\FileStorage\File;
use App\Application\Domain\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Snap\MimeType;
use App\Application\Domain\Snap\Snap;
use App\Application\Domain\Snap\SnapFactory;
use App\Application\Domain\Snap\SnapRepositoryInterface;
use App\Application\UseCase\Snap\DeleteUserSnaps\DeleteUserSnapsRequest;
use App\Application\UseCase\Snap\DeleteUserSnaps\DeleteUserSnapsUseCase;
use App\Tests\FilesnapTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use Random\RandomException;
use Symfony\Component\Uid\Uuid;

final class DeleteUserSnapsTest extends FilesnapTestCase
{
    /**
     * @return list<array{0:Uuid,1:list<Snap>}>
     *
     * @throws Exception
     * @throws FileNotFoundException
     * @throws RandomException
     */
    public static function sameUserIdSnapsProvider(): array
    {
        $fileRepositoryStub = self::createConfiguredStub(FileStorageInterface::class, [
            'get' => new File('/this/is/an/absolute/path/to/file.jpg'),
        ]);

        $snapFactory = new SnapFactory($fileRepositoryStub);
        $userId = Uuid::v7();

        $snaps = [];
        for ($i = 0; $i < 5; ++$i) {
            $snaps[] = $snapFactory->create(
                id: Uuid::v4(),
                userId: $userId,
                originalFilename: 'original-filename.jpg',
                mimeType: MimeType::ImageJpeg,
                creationDate: self::getRandomDateTime(),
                lastSeenDate: self::getRandomDateTime()
            );
        }

        return [
            [$userId, $snaps],
        ];
    }

    /**
     * @return list<array{0:Uuid,1:list<Snap>}>
     *
     * @throws Exception
     * @throws FileNotFoundException
     * @throws RandomException
     */
    public static function differentUserIdsSnapsProvider(): array
    {
        $fileRepositoryStub = self::createConfiguredStub(FileStorageInterface::class, [
            'get' => new File('/this/is/an/absolute/path/to/file.jpg'),
        ]);

        $snapFactory = new SnapFactory($fileRepositoryStub);
        $userId = Uuid::v7();

        $snaps = [];
        for ($i = 0; $i < 4; ++$i) {
            $snaps[] = $snapFactory->create(
                id: Uuid::v4(),
                userId: $userId,
                originalFilename: 'original-filename.jpg',
                mimeType: MimeType::ImageJpeg,
                creationDate: self::getRandomDateTime(),
                lastSeenDate: self::getRandomDateTime()
            );
        }

        $snaps[] = $snapFactory->create(
            id: Uuid::v4(),
            userId: Uuid::v7(),
            originalFilename: 'original-filename.jpg',
            mimeType: MimeType::ImageJpeg,
            creationDate: self::getRandomDateTime(),
            lastSeenDate: self::getRandomDateTime()
        );

        return [
            [$userId, $snaps],
        ];
    }

    /**
     * @param list<Snap> $snaps
     *
     * @throws Exception
     * @throws InvalidRequestParameterException
     * @throws UnauthorizedDeletionException
     * @throws UnknownSnapsException
     */
    #[DataProvider('sameUserIdSnapsProvider')]
    public function testItDeletesUserSnaps(Uuid $userId, array $snaps): void
    {
        $snapIds = array_map(
            static fn (Snap $snap) => $snap->getId(),
            $snaps
        );

        $snapRepositoryMock = $this->createMock(SnapRepositoryInterface::class);
        $fileRepositoryMock = $this->createMock(FileStorageInterface::class);

        $snapRepositoryMock
            ->expects($this->once())
            ->method('findByIds')
            ->with($snapIds)
            ->willReturn($snaps);

        $snapRepositoryMock
            ->expects($this->once())
            ->method('deleteByIds')
            ->with($userId, $snapIds);

        $invokedCount = $this->exactly(count($snapIds));
        $fileRepositoryMock
            ->expects($invokedCount)
            ->method('delete')
            ->willReturnCallback(function ($snap) use ($invokedCount, $snaps) {
                $expectedSnap = $snaps[$invokedCount->numberOfInvocations() - 1];
                self::assertSame($expectedSnap, $snap);
            });

        $useCase = new DeleteUserSnapsUseCase($snapRepositoryMock, $fileRepositoryMock);
        $useCase(new DeleteUserSnapsRequest($userId, $snapIds));
    }

    /**
     * @param list<Snap> $snaps
     *
     * @throws Exception
     * @throws InvalidRequestParameterException
     * @throws UnauthorizedDeletionException
     * @throws UnknownSnapsException
     */
    #[DataProvider('differentUserIdsSnapsProvider')]
    public function testItFailsUnauthorizedDeletion(Uuid $userId, array $snaps): void
    {
        $snapIds = array_map(
            static fn (Snap $snap) => $snap->getId(),
            $snaps
        );

        $snapRepositoryMock = $this->createMock(SnapRepositoryInterface::class);
        $fileRepositoryMock = $this->createMock(FileStorageInterface::class);

        $snapRepositoryMock
            ->expects($this->once())
            ->method('findByIds')
            ->with($snapIds)
            ->willReturn($snaps);

        $snapRepositoryMock
            ->expects($this->never())
            ->method('deleteByIds');

        $fileRepositoryMock
            ->expects($this->never())
            ->method('delete');

        $this->expectException(UnauthorizedDeletionException::class);

        $useCase = new DeleteUserSnapsUseCase($snapRepositoryMock, $fileRepositoryMock);
        $useCase(new DeleteUserSnapsRequest($userId, $snapIds));
    }

    /**
     * @throws UnauthorizedDeletionException
     * @throws UnknownSnapsException
     * @throws Exception
     * @throws InvalidRequestParameterException
     */
    public function testItFailsInvalidRequestParameter(): void
    {
        $snapRepositoryMock = $this->createMock(SnapRepositoryInterface::class);
        $fileRepositoryMock = $this->createMock(FileStorageInterface::class);

        $snapRepositoryMock
            ->expects($this->never())
            ->method('findByIds');

        $snapRepositoryMock
            ->expects($this->never())
            ->method('deleteByIds');

        $fileRepositoryMock
            ->expects($this->never())
            ->method('delete');

        $this->expectException(InvalidRequestParameterException::class);

        $useCase = new DeleteUserSnapsUseCase($snapRepositoryMock, $fileRepositoryMock);
        $useCase(new DeleteUserSnapsRequest(Uuid::v7(), []));
    }

    /**
     * @param list<Snap> $snaps
     *
     * @throws Exception
     * @throws InvalidRequestParameterException
     * @throws UnauthorizedDeletionException
     * @throws UnknownSnapsException
     */
    #[DataProvider('sameUserIdSnapsProvider')]
    public function testItFailsUnknownSnaps(Uuid $userId, array $snaps): void
    {
        $snapIds = array_map(
            static fn (Snap $snap) => $snap->getId(),
            $snaps
        );

        $snapIdlistWithUnknownSnap = [...$snapIds, Uuid::v4()];

        $snapRepositoryMock = $this->createMock(SnapRepositoryInterface::class);
        $fileRepositoryMock = $this->createMock(FileStorageInterface::class);

        $snapRepositoryMock
            ->expects($this->once())
            ->method('findByIds')
            ->with($snapIdlistWithUnknownSnap)
            ->willReturn($snaps);

        $snapRepositoryMock
            ->expects($this->never())
            ->method('deleteByIds');

        $fileRepositoryMock
            ->expects($this->never())
            ->method('delete');

        $this->expectException(UnknownSnapsException::class);

        $useCase = new DeleteUserSnapsUseCase($snapRepositoryMock, $fileRepositoryMock);
        $useCase(new DeleteUserSnapsRequest($userId, $snapIdlistWithUnknownSnap));
    }
}
