<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter\Format;

use App\Application\Domain\Snap\Snap;
use App\Infrastructure\Symfony\Service\FormatConverter\AbstractFormat;
use App\Infrastructure\Symfony\Service\FormatConverter\GdService;
use App\Infrastructure\Symfony\Service\FormatConverter\StorageInterface;
use Symfony\Component\HttpFoundation\File\File;

final readonly class Avif extends AbstractFormat
{
    public function __construct(
        StorageInterface $storage,
        private int $quality = 90,
    ) {
        if ($this->quality < 0 || $this->quality > 100) {
            throw new \InvalidArgumentException('Quality must be between 0 and 100');
        }

        parent::__construct($storage);
    }

    public static function getExtension(): string
    {
        return 'avif';
    }

    protected function convertFile(Snap $snap): File
    {
        $gdImage = GdService::getSnapGdImage($snap);
        $tempFilePath = sprintf('%s/%s.%s', sys_get_temp_dir(), $snap->getId()->toBase58(), self::getExtension());

        if (imageavif($gdImage, $tempFilePath, $this->quality) === false) {
            throw new \RuntimeException('Error at avif image creation');
        }

        return new File($tempFilePath);
    }
}
