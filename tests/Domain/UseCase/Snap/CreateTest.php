<?php

declare(strict_types=1);

namespace App\Tests\Domain\UseCase\Snap;

use App\Application\Domain\Entity\Snap\Exception\FileNotFoundException;
use App\Application\Domain\Entity\Snap\Exception\FileSizeTooBigException;
use App\Application\Domain\Entity\Snap\Exception\UnsupportedFileTypeException;
use App\Application\Domain\Entity\Snap\Factory\SnapFactory;
use App\Application\Domain\Entity\Snap\FileStorage\File;
use App\Application\Domain\Entity\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Entity\Snap\MimeType;
use App\Application\Domain\Entity\Snap\Repository\SnapRepositoryInterface;
use App\Application\Domain\Entity\Snap\Snap;
use App\Application\UseCase\Snap\Create\CreateSnapRequest;
use App\Application\UseCase\Snap\Create\CreateSnapUseCase;
use App\Tests\FilesnapTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Uid\Uuid;

final class CreateTest extends FilesnapTestCase
{
    public static function itCreatesSnapProvider(): array
    {
        return array_map(
            static function (MimeType $mimeType) {
                $originalFilenameBase = 'the-original-filename';
                $fileAbsolutePathBase = '/this/is/an/absolute/path/to/a/file';

                [$originalFilename, $fileAbsolutePath] = match ($mimeType) {
                    MimeType::ImageJpeg => [$originalFilenameBase . '.jpg', $fileAbsolutePathBase . '.jpg'],
                    MimeType::ImagePng => [$originalFilenameBase . '.png', $fileAbsolutePathBase . '.png'],
                    MimeType::ImageGif => [$originalFilenameBase . '.gif', $fileAbsolutePathBase . '.gif'],
                    MimeType::VideoWebm => [$originalFilenameBase . '.webm', $fileAbsolutePathBase . '.webm'],
                    MimeType::VideoMp4 => [$originalFilenameBase . '.mp4', $fileAbsolutePathBase . '.mp4']
                };

                return [$originalFilename, $fileAbsolutePath, $mimeType->value];
            },
            MimeType::cases()
        );
    }

    /**
     * @throws Exception
     * @throws FileNotFoundException
     * @throws FileSizeTooBigException
     * @throws UnsupportedFileTypeException
     */
    #[DataProvider('itCreatesSnapProvider')]
    public function testItCreatesSnap(string $originalFilename, string $fileAbsolutePath, string $fileMimeType): void
    {
        $userId = Uuid::v7();
        $file = new File($fileAbsolutePath);

        $fileStorageStub = $this->createConfiguredStub(FileStorageInterface::class, [
            'getFileMaximumAuthorizedBytesSize' => 100,
            'get' => $file,
        ]);

        $snapRepositoryMock = $this->createMock(SnapRepositoryInterface::class);

        $snapRepositoryMock
            ->expects($this->once())
            ->method('create');

        $snapFactory = new SnapFactory($fileStorageStub);
        $useCase = new CreateSnapUseCase($snapRepositoryMock, $fileStorageStub, $snapFactory);

        $request = new CreateSnapRequest(
            userId: $userId,
            fileOriginalName: $originalFilename,
            fileMimeType: $fileMimeType,
            filePath: '/this/is/a/path/to/a/file',
            fileBytesSize: 10
        );

        $response = $useCase($request);
        $snap = $response->getSnap();

        $this->assertInstanceOf(Snap::class, $snap);
        $this->assertInstanceOf(Uuid::class, $snap->getId());
        $this->assertSame($userId, $snap->getUserId());
        $this->assertSame($originalFilename, $snap->getOriginalFilename());
        $this->assertSame(MimeType::tryFrom($fileMimeType), $snap->getMimeType());
        $this->assertSame(time(), $snap->getCreationDate()->getTimestamp());
        $this->assertNull($snap->getLastSeenDate());
        $this->assertSame($file->getAbsolutePath(), $snap->getFile()->getAbsolutePath());
    }

    /**
     * @throws Exception
     * @throws FileNotFoundException
     * @throws FileSizeTooBigException
     * @throws UnsupportedFileTypeException
     */
    public function testItFailsFileTooBig(): void
    {
        $fileStorageStub = $this->createConfiguredStub(FileStorageInterface::class, [
            'getFileMaximumAuthorizedBytesSize' => 10,
        ]);

        $snapRepositoryStub = $this->createStub(SnapRepositoryInterface::class);
        $snapFactory = new SnapFactory($fileStorageStub);
        $useCase = new CreateSnapUseCase($snapRepositoryStub, $fileStorageStub, $snapFactory);

        $request = new CreateSnapRequest(
            userId: Uuid::v7(),
            fileOriginalName: 'original-file-name.jpg',
            fileMimeType: MimeType::ImageJpeg->value,
            filePath: '/this/is/a/path/to/a/file.jpg',
            fileBytesSize: 100
        );

        $this->expectException(FileSizeTooBigException::class);

        $useCase($request);
    }

    /**
     * @throws Exception
     * @throws FileNotFoundException
     * @throws FileSizeTooBigException
     * @throws UnsupportedFileTypeException
     */
    public function testItFailsUnsupportedFileType(): void
    {
        $fileStorageStub = $this->createConfiguredStub(FileStorageInterface::class, [
            'getFileMaximumAuthorizedBytesSize' => 100,
        ]);

        $snapRepositoryStub = $this->createStub(SnapRepositoryInterface::class);
        $snapFactory = new SnapFactory($fileStorageStub);
        $useCase = new CreateSnapUseCase($snapRepositoryStub, $fileStorageStub, $snapFactory);

        $request = new CreateSnapRequest(
            userId: Uuid::v7(),
            fileOriginalName: 'original-file-name.bmp',
            fileMimeType: 'image/bmp',
            filePath: '/this/is/a/path/to/a/file.bmp',
            fileBytesSize: 10
        );

        $this->expectException(UnsupportedFileTypeException::class);

        $useCase($request);
    }

    /**
     * @throws Exception
     * @throws FileNotFoundException
     * @throws FileSizeTooBigException
     * @throws UnsupportedFileTypeException
     */
    public function testItFailsFileNotFound(): void
    {
        $fileStorageStub = $this->createConfiguredStub(FileStorageInterface::class, [
            'getFileMaximumAuthorizedBytesSize' => 100,
            'get' => null,
        ]);

        $snapRepositoryStub = $this->createStub(SnapRepositoryInterface::class);
        $snapFactory = new SnapFactory($fileStorageStub);
        $useCase = new CreateSnapUseCase($snapRepositoryStub, $fileStorageStub, $snapFactory);

        $request = new CreateSnapRequest(
            userId: Uuid::v7(),
            fileOriginalName: 'original-file-name.jpg',
            fileMimeType: MimeType::ImageJpeg->value,
            filePath: '/this/is/a/path/to/a/file.jpg',
            fileBytesSize: 10
        );

        $this->expectException(FileNotFoundException::class);

        $useCase($request);
    }
}
