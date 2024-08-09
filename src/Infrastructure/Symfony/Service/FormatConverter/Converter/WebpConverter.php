<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter\Converter;

use App\Application\Domain\Snap\MimeType;
use App\Application\Domain\Snap\Snap;
use Symfony\Component\Filesystem\Filesystem;

final readonly class WebpConverter implements FormatConverterInterface
{
    private const int QUALITY = 90;

    public function __construct(
        private Filesystem $filesystem = new Filesystem()
    ) {
    }

    public function getFileAbsolutePath(Snap $snap): string
    {
        return $snap->getFile()->getAbsolutePath() . '.webp';
    }

    /**
     * @throws \Exception
     */
    public function convert(Snap $snap): string
    {
        $snapAbsolutePath = $snap->getFile()->getAbsolutePath();
        $snapMimeType = $snap->getMimeType();

        $gdImage = match ($snapMimeType) {
            MimeType::ImageJpeg => imagecreatefromjpeg($snapAbsolutePath),
            MimeType::ImagePng => imagecreatefrompng($snapAbsolutePath),
            MimeType::ImageGif => imagecreatefromgif($snapAbsolutePath),
            default => throw new \RuntimeException(sprintf('You can\'t generate a webp from a %s file.', $snapMimeType->value))
        };

        if ($gdImage === false) {
            throw new \RuntimeException('GdImage could not be created.');
        }

        $webpAbsolutePath = $this->getFileAbsolutePath($snap);

        if (imagewebp($gdImage, $webpAbsolutePath, self::QUALITY) === false) {
            throw new \RuntimeException('Error at webp image creation');
        }

        return $webpAbsolutePath;
    }

    public function fileExists(Snap $snap): bool
    {
        return $this->filesystem->exists($this->getFileAbsolutePath($snap));
    }

    public function deleteConvertedFile(Snap $snap): void
    {
        $this->filesystem->remove($this->getFileAbsolutePath($snap));
    }
}
