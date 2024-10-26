<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter\Converter\Webp;

use App\Application\Domain\Snap\MimeType;
use App\Application\Domain\Snap\Snap;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\AbstractConverter;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\FormatStorageInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

final readonly class WebpConverter extends AbstractConverter
{
    private const int QUALITY = 90;

    public function __construct(
        #[Autowire(service: 'webp.local.storage')] FormatStorageInterface $formatStorage,
        private Filesystem $filesystem = new Filesystem(),
    ) {
        parent::__construct($formatStorage);
    }

    protected function createConvertedFile(Snap $snap): File
    {
        $snapAbsolutePath = $snap->getFile()->getAbsolutePath();
        $snapMimeType = $snap->getMimeType();

        $gdImage = match ($snapMimeType) {
            MimeType::ImageJpeg => imagecreatefromjpeg($snapAbsolutePath),
            MimeType::ImagePng => imagecreatefrompng($snapAbsolutePath),
            MimeType::ImageGif => imagecreatefromgif($snapAbsolutePath),
            MimeType::ImageWebp => imagecreatefromwebp($snapAbsolutePath),
            default => throw new \RuntimeException(sprintf('You can\'t generate a webp from a %s file.', $snapMimeType->value)),
        };

        if ($gdImage === false) {
            throw new \RuntimeException('GdImage could not be created.');
        }

        $tempPath = $this->getTempFilePath($snap);

        if (imagewebp($gdImage, $tempPath, self::QUALITY) === false) {
            throw new \RuntimeException('Error at webp image creation');
        }

        return new File($tempPath);
    }

    protected function cleanUp(Snap $snap): void
    {
        $this->filesystem->remove($this->getTempFilePath($snap));
    }

    private function getTempFilePath(Snap $snap): string
    {
        return sprintf('%s/%s.webp', sys_get_temp_dir(), $snap->getId()->toBase58());
    }
}
