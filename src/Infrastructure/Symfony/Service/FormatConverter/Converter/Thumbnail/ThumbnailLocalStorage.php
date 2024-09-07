<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter\Converter\Thumbnail;

use App\Application\Domain\Snap\Snap;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\FormatStorageInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

final readonly class ThumbnailLocalStorage implements FormatStorageInterface
{
    public function __construct(
        #[Autowire(param: 'app.thumbnail_directory')] private string $thumbnailDirectory,
        private Filesystem $filesystem = new Filesystem(),
    ) {
    }

    public function save(Snap $snap, File $file): void
    {
        if ($this->filesystem->exists($this->thumbnailDirectory) === false) {
            $this->filesystem->mkdir($this->thumbnailDirectory);
        }

        $this->filesystem->rename($file->getPathname(), $this->getFileAbsolutePath($snap));
    }

    public function get(Snap $snap): ?File
    {
        $absolutePath = $this->getFileAbsolutePath($snap);

        return $this->filesystem->exists($absolutePath)
            ? new File($absolutePath)
            : null;
    }

    public function delete(Snap $snap): void
    {
        $this->filesystem->remove($this->getFileAbsolutePath($snap));
    }

    private function getFileAbsolutePath(Snap $snap): string
    {
        return sprintf('%s/%s.thumbnail', $this->thumbnailDirectory, $snap->getId()->toBase58());
    }
}
