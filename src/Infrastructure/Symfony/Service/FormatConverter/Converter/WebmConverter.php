<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter\Converter;

use App\Application\Domain\Snap\Snap;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\WebM;
use Symfony\Component\Filesystem\Filesystem;

final readonly class WebmConverter implements FormatConverterInterface
{
    public function __construct(
        private Filesystem $filesystem = new Filesystem()
    ) {
    }

    public function getFileAbsolutePath(Snap $snap): string
    {
        return $snap->getFile()->getAbsolutePath() . '.webm';
    }

    /**
     * @throws \Exception
     */
    public function convert(Snap $snap): string
    {
        if ($snap->isVideo() === false) {
            throw new \RuntimeException(sprintf('You can\'t generate a webm from a %s file.', $snap->getMimeType()->value));
        }

        $webmAbsolutePath = $this->getFileAbsolutePath($snap);

        FFMpeg::create()
            ->open($snap->getFile()->getAbsolutePath())
            ->save(new WebM(), $webmAbsolutePath);

        return $webmAbsolutePath;
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
