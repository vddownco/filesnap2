<?php

declare(strict_types=1);

namespace App\Tests\Domain\UseCase\Snap;

use App\Application\Domain\Entity\Snap\Exception\FileNotFoundException;
use App\Application\Domain\Entity\Snap\Exception\UnauthorizedDeletionException;
use App\Application\Domain\Entity\Snap\Factory\SnapFactory;
use App\Application\Domain\Entity\Snap\FileStorage\File;
use App\Application\Domain\Entity\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Entity\Snap\MimeType;
use App\Application\Domain\Entity\Snap\Repository\SnapRepositoryInterface;
use App\Application\Domain\Entity\Snap\Snap;
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
     * @throws UnauthorizedDeletionException
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

        $fileRepositoryMock
            ->expects($this->exactly(count($snapIds)))
            ->method('delete');

        $useCase = new DeleteUserSnapsUseCase($snapRepositoryMock, $fileRepositoryMock);
        $useCase(new DeleteUserSnapsRequest($userId, $snapIds));
    }

    /**
     * @param list<Snap> $snaps
     *
     * @throws Exception
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
}
