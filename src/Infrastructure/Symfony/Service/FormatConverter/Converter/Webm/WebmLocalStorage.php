<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter\Converter\Webm;

use App\Application\Domain\Snap\Snap;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\FormatStorageInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

final readonly class WebmLocalStorage implements FormatStorageInterface
{
    public function __construct(
        #[Autowire(param: 'app.converted_upload_directory')] private string $convertedUploadDirectory,
        private Filesystem $filesystem = new Filesystem()
    ) {
    }

    public function save(Snap $snap, File $file): void
    {
        $userDirectory = sprintf('%s/%s/', $this->convertedUploadDirectory, $snap->getUserId()->toBase58());

        if ($this->filesystem->exists($userDirectory) === false) {
            $this->filesystem->mkdir($userDirectory);
        }

        $this->filesystem->rename($file->getPathname(), $this->getFileAbsolutePath($snap));
    }

    public function get(Snap $snap): ?File
    {
        $fileAbsolutePath = $this->getFileAbsolutePath($snap);

        return $this->filesystem->exists($fileAbsolutePath) === true
            ? new File($fileAbsolutePath)
            : null;
    }

    public function delete(Snap $snap): void
    {
        $this->filesystem->remove($this->getFileAbsolutePath($snap));
    }

    private function getFileAbsolutePath(Snap $snap): string
    {
        return sprintf(
            '%s/%s/%s.webm',
            $this->convertedUploadDirectory,
            $snap->getUserId()->toBase58(),
            $snap->getId()->toBase58()
        );
    }
}
