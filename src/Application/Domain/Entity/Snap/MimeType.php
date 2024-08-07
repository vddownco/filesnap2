<?php

declare(strict_types=1);

namespace App\Application\Domain\Entity\Snap;

use App\Application\Domain\Entity\Snap\Exception\UnsupportedFileTypeException;

enum MimeType: string
{
    /** @var list<self> */
    public const array IMAGES_MIME_TYPES = [
        self::ImageJpeg,
        self::ImagePng,
        self::ImageGif,
    ];

    /** @var list<self> */
    public const array VIDEO_MIME_TYPES = [
        self::VideoWebm,
        self::VideoMp4,
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

        if ($enum === null) {
            throw new UnsupportedFileTypeException($mimeType);
        }

        return $enum;
    }

    public function isImage(): bool
    {
        return in_array($this, self::IMAGES_MIME_TYPES, true);
    }

    public function isVideo(): bool
    {
        return in_array($this, self::VIDEO_MIME_TYPES, true);
    }
}
