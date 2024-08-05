<?php

declare(strict_types=1);

namespace App\Infrastructure\Entity\Snap\FileStorage;

use App\Application\Domain\Entity\Snap\FileStorage\File;
use App\Application\Domain\Entity\Snap\FileStorage\FileMetadata;
use App\Application\Domain\Entity\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Entity\Snap\Snap;
use App\Infrastructure\Symfony\Service\FormatConverter\FormatConverterService;
use App\Infrastructure\Symfony\Service\ThumbnailService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Uid\Uuid;

final readonly class LocalFileStorage implements FileStorageInterface
{
    public function __construct(
        #[Autowire(param: 'app.project_directory')] private string $projectDirectory,
        #[Autowire(param: 'app.upload.relative_directory')] private string $uploadRelativeDirectory,
        #[Autowire(param: 'app.upload.bytes_max_filesize')] private int $uploadBytesMaxFilesize,
        private ThumbnailService $thumbnailService,
        private Filesystem $filesystem = new Filesystem()
    ) {
    }

    public function getFileMaximumAuthorizedBytesSize(): int
    {
        return $this->uploadBytesMaxFilesize;
    }

    public function store(Uuid $snapId, Uuid $snapUserId, FileMetadata $fileMetadata): void
    {
        $userPersonalUploadDirectory = sprintf(
            '%s%s/%s/',
            $this->projectDirectory,
            $this->uploadRelativeDirectory,
            $snapUserId->toBase58()
        );

        if ($this->filesystem->exists($userPersonalUploadDirectory) === false) {
            $this->filesystem->mkdir($userPersonalUploadDirectory);
        }

        $this->filesystem->copy(
            $fileMetadata->getPath(),
            $userPersonalUploadDirectory . $snapId->toBase58()
        );
    }

    public function delete(Snap $snap): void
    {
        $filePath = sprintf(
            '%s%s/%s/%s',
            $this->projectDirectory,
            $this->uploadRelativeDirectory,
            $snap->getUserId()->toBase58(),
            $snap->getId()->toBase58()
        );

        $this->filesystem->remove($filePath);
        $this->thumbnailService->delete($snap->getId());
        FormatConverterService::deleteAllConvertedFiles($snap);
    }

    public function get(Uuid $snapId, Uuid $snapUserId): ?File
    {
        $filePath = sprintf(
            '%s%s/%s/%s',
            $this->projectDirectory,
            $this->uploadRelativeDirectory,
            $snapUserId->toBase58(),
            $snapId->toBase58()
        );

        if ($this->filesystem->exists($filePath) === false) {
            return null;
        }

        return new File($filePath);
    }
}
