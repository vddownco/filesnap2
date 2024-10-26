<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter\Converter\Webm;

use App\Application\Domain\Snap\Snap;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\AbstractConverter;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\FormatStorageInterface;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\WebM;
use FFMpeg\Media\Video;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

final readonly class WebmConverter extends AbstractConverter
{
    private const int DEFAULT_VIDEO_KILO_BITRATE = 1000;
    private const int DEFAULT_AUDIO_KILO_BITRATE = 196;

    public function __construct(
        #[Autowire(service: 'webm.local.storage')] FormatStorageInterface $formatStorage,
        private Filesystem $filesystem = new Filesystem(),
    ) {
        parent::__construct($formatStorage);
    }

    protected function createConvertedFile(Snap $snap): File
    {
        if ($snap->isVideo() === false) {
            throw new \RuntimeException(sprintf('You can\'t generate a webm from a %s file.', $snap->getMimeType()->value));
        }

        $bitrate = self::DEFAULT_VIDEO_KILO_BITRATE;
        $video = FFMpeg::create()->open($snap->getFile()->getAbsolutePath());

        if ($video instanceof Video === false) {
            throw new \RuntimeException('Opened file is not a video');
        }

        $videoStream = $video->getStreams()->videos()->first();

        if ($videoStream !== null) {
            $streamBitrate = $videoStream->get('bit_rate');

            if (is_numeric($streamBitrate) === true) {
                $bitrate = (int) ceil(((int) $streamBitrate) / 1000);
            }
        }

        $format = (new WebM())
            ->setKiloBitrate($bitrate)
            ->setAudioKiloBitrate(self::DEFAULT_AUDIO_KILO_BITRATE);

        $tempPath = $this->getTempFilePath($snap);
        $video->save($format, $tempPath);

        return new File($tempPath);
    }

    protected function cleanUp(Snap $snap): void
    {
        $this->filesystem->remove($this->getTempFilePath($snap));
    }

    private function getTempFilePath(Snap $snap): string
    {
        return sprintf('%s/%s.webm', sys_get_temp_dir(), $snap->getId()->toBase58());
    }
}
