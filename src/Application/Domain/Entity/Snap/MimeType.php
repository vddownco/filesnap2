<?php
declare(strict_types=1);

namespace App\Application\Domain\Entity\Snap;

use App\Application\Domain\Entity\Snap\Exception\UnsupportedFileTypeException;

enum MimeType: string
{
    /** @var self[] */
    public const imageMimeTypes = [
        self::ImageJpeg,
        self::ImagePng,
        self::ImageGif
    ];

    /** @var self[] */
    public const videoMimeTypes = [
        self::VideoWebm,
        self::VideoMp4
    ];

    case ImageJpeg = 'image/jpeg';
    case ImagePng = 'image/png';
    case ImageGif = 'image/gif';
    case VideoWebm = 'video/webm';
    case VideoMp4 = 'video/mp4';

    /**
     * @throws UnsupportedFileTypeException
     */
    public static function fromString(string $mimeType): self
    {
        $enum = self::tryFrom($mimeType);

        if (null === $enum) {
            throw new UnsupportedFileTypeException($mimeType);
        }

        return $enum;
    }
}