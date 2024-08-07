<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service;

use App\Application\Domain\Entity\Snap\Snap;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Media\Video;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Uid\Uuid;

final readonly class ThumbnailService
{
    private const int RESIZE_WIDTH = 240;

    public function __construct(
        #[Autowire(param: 'app.public_directory')] private string $publicDirectory,
        private Filesystem $filesystem = new Filesystem()
    ) {
    }

    /**
     * @throws \Exception
     */
    public function generate(Snap $snap): void
    {
        $imagine = new Imagine();
        $thumbnailAbsolutePath = $this->getThumbnailAbsolutePath($snap->getId());

        if ($snap->isImage() === true) {
            $imagineImage = $this->resize($imagine->open($snap->getFile()->getAbsolutePath()));
            $this->save($imagineImage, $thumbnailAbsolutePath);
        }

        if ($snap->isVideo() === true) {
            $tmpFilename = $this->filesystem->tempnam(sys_get_temp_dir(), 'ffmpeg_tmp_', '.jpeg');

            $ffmpegVideo = FFMpeg::create()->open($snap->getFile()->getAbsolutePath());

            if ($ffmpegVideo instanceof Video === false) {
                throw new \RuntimeException('Opened file is not a video');
            }

            $ffmpegVideo
                ->frame(TimeCode::fromSeconds(1))
                ->save($tmpFilename);

            $imagineImage = $this->resize($imagine->open($tmpFilename));
            $this->save($imagineImage, $thumbnailAbsolutePath);
        }
    }

    public function delete(Uuid $snapId): void
    {
        $this->filesystem->remove($this->getThumbnailAbsolutePath($snapId));
    }

    private function getThumbnailAbsolutePath(Uuid $snapId): string
    {
        return sprintf('%s/snap/%s.thumbnail', $this->publicDirectory, $snapId->toBase58());
    }

    private function resize(ImageInterface $image): ImageInterface
    {
        $height = ($image->getSize()->getHeight() / $image->getSize()->getWidth()) * self::RESIZE_WIDTH;
        $image->resize(new Box(self::RESIZE_WIDTH, $height));

        return $image;
    }

    private function save(ImageInterface $image, string $filepath): void
    {
        $tmpFilename = $this->filesystem->tempnam(sys_get_temp_dir(), 'imagine_tmp_', '.jpeg');
        $image->save($tmpFilename);
        $this->filesystem->rename($tmpFilename, $filepath);
    }
}
