<?php

declare(strict_types=1);

namespace App\Application\UseCase\Snap\Create;

use App\Application\Domain\Snap\Exception\FileNotFoundException;
use App\Application\Domain\Snap\Exception\FileSizeTooBigException;
use App\Application\Domain\Snap\Exception\UnsupportedFileTypeException;
use App\Application\Domain\Snap\FileStorage\FileMetadata;
use App\Application\Domain\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Snap\MimeType;
use App\Application\Domain\Snap\SnapFactory;
use App\Application\Domain\Snap\SnapRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final readonly class CreateSnapUseCase
{
    public function __construct(
        private SnapRepositoryInterface $snapRepository,
        private FileStorageInterface $fileStorage,
        private SnapFactory $snapFactory
    ) {
    }

    /**
     * @throws UnsupportedFileTypeException
     * @throws FileSizeTooBigException
     * @throws FileNotFoundException
     * @throws \Exception
     */
    public function __invoke(CreateSnapRequest $request): CreateSnapResponse
    {
        $fileMaximumAuthorizedBytesSize = $this->fileStorage->getFileMaximumAuthorizedBytesSize();

        if ($request->getFileBytesSize() > $fileMaximumAuthorizedBytesSize) {
            throw new FileSizeTooBigException($fileMaximumAuthorizedBytesSize);
        }

        $snapMimeType = MimeType::tryFrom($request->getFileMimeType());

        if ($snapMimeType === null) {
            throw new UnsupportedFileTypeException($request->getFileMimeType());
        }

        $snapId = Uuid::v4();

        $this->fileStorage->store(
            $snapId,
            $request->getUserId(),
            new FileMetadata(
                $request->getFileOriginalName(),
                $request->getFilePath(),
                $snapMimeType
            )
        );

        $snap = $this->snapFactory->create(
            id: $snapId,
            userId: $request->getUserId(),
            originalFilename: $request->getFileOriginalName(),
            mimeType: $snapMimeType,
            creationDate: new \DateTimeImmutable(),
            lastSeenDate: null
        );

        try {
            $this->snapRepository->create($snap);
        } catch (\Exception $e) {
            $this->fileStorage->delete($snap);
            throw $e;
        }

        return new CreateSnapResponse($snap);
    }
}
