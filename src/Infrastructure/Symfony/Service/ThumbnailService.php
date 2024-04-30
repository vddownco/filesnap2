<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service;

use App\Application\Domain\Entity\Snap\Snap;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Uid\Uuid;

final readonly class ThumbnailService
{
    public function __construct(
        #[Autowire(param: 'app.public_directory')] private string $publicDirectory,
        private Filesystem $filesystem = new Filesystem()
    ) {
    }

    private function getThumbnailAbsolutePathDotThumbnail(Uuid $snapId): string
    {
        return sprintf('%s/snap/%s.thumbnail', $this->publicDirectory, $snapId->toBase58());
    }

    public function generate(Snap $snap): void
    {
        $thumbnailAbsolutePathDotJpeg = sprintf(
            '%s/snap/%s.jpeg',
            $this->publicDirectory,
            $snap->getId()->toBase58()
        );

        $thumbnailAbsolutePathDotThumbnail = $this->getThumbnailAbsolutePathDotThumbnail($snap->getId());

        if ($snap->isImage() === true) {
            $imagine = new Imagine();
            $imagineImage = $imagine->open($snap->getFile()->getAbsolutePath());

            $width = 240;
            $height = ($imagineImage->getSize()->getHeight() / $imagineImage->getSize()->getWidth()) * $width;

            $imagineImage->resize(new Box($width, $height));
            $imagineImage->save($thumbnailAbsolutePathDotJpeg);

            $this->filesystem->rename(
                $thumbnailAbsolutePathDotJpeg,
                $thumbnailAbsolutePathDotThumbnail
            );
        } elseif ($snap->isVideo() === true) {
            $videoThumbnail = sprintf('%s/video_thumbnail.jpg', $this->publicDirectory);

            $this->filesystem->copy(
                $videoThumbnail,
                $thumbnailAbsolutePathDotThumbnail
            );
        }
    }

    public function delete(Uuid $snapId): void
    {
        $this->filesystem->remove($this->getThumbnailAbsolutePathDotThumbnail($snapId));
    }
}
