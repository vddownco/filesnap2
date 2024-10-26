<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter\Converter\Thumbnail;

use App\Application\Domain\Snap\Snap;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\AbstractConverter;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\FormatStorageInterface;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Media\Video;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

final readonly class ThumbnailConverter extends AbstractConverter
{
    private const int RESIZE_WIDTH = 240;

    public function __construct(
        #[Autowire(service: 'thumbnail.local.storage')] FormatStorageInterface $formatStorage,
        private Filesystem $filesystem = new Filesystem(),
    ) {
        parent::__construct($formatStorage);
    }

    protected function createConvertedFile(Snap $snap): File
    {
        $imagine = new Imagine();
        $tempFilePath = $this->getTempFilePath($snap);

        if ($snap->isImage() === true) {
            $imagineImage = $this->resize($imagine->open($snap->getFile()->getAbsolutePath()));
            $imagineImage->save($tempFilePath);
        }

        if ($snap->isVideo() === true) {
            $tempFrameFilePath = $this->getTempFrameFilePath($snap);
            $ffmpegVideo = FFMpeg::create()->open($snap->getFile()->getAbsolutePath());

            if ($ffmpegVideo instanceof Video === false) {
                throw new \RuntimeException('Opened file is not a video');
            }

            $ffmpegVideo
                ->frame(TimeCode::fromSeconds(1))
                ->save($tempFrameFilePath);

            $imagineImage = $this->resize($imagine->open($tempFrameFilePath));
            $imagineImage->save($tempFilePath);
        }

        return new File($tempFilePath);
    }

    protected function cleanUp(Snap $snap): void
    {
        $this->filesystem->remove([$this->getTempFilePath($snap), $this->getTempFrameFilePath($snap)]);
    }

    private function resize(ImageInterface $image): ImageInterface
    {
        $height = ($image->getSize()->getHeight() / $image->getSize()->getWidth()) * self::RESIZE_WIDTH;
        $image->resize(new Box(self::RESIZE_WIDTH, $height));

        return $image;
    }

    private function getTempFilePath(Snap $snap): string
    {
        return sprintf('%s/thumbnail_tmp_%s.jpeg', sys_get_temp_dir(), $snap->getId()->toBase58());
    }

    private function getTempFrameFilePath(Snap $snap): string
    {
        return sprintf('%s/ffmpeg_tmp_frame_%s.jpeg', sys_get_temp_dir(), $snap->getId()->toBase58());
    }
}
