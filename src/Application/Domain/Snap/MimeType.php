<?php

declare(strict_types=1);

namespace App\Application\Domain\Snap;

enum MimeType: string
{
    case ImageJpeg = 'image/jpeg';
    case ImagePng = 'image/png';
    case ImageGif = 'image/gif';
    case ImageWebp = 'image/webp';
    case VideoWebm = 'video/webm';
    case VideoMp4 = 'video/mp4';

    /** @var list<self> */
    public const array IMAGE_MIME_TYPES = [
        self::ImageJpeg,
        self::ImagePng,
        self::ImageGif,
        self::ImageWebp,
    ];

    /** @var list<self> */
    public const array VIDEO_MIME_TYPES = [
        self::VideoWebm,
        self::VideoMp4,
    ];

    public function isImage(): bool
    {
        return in_array($this, self::IMAGE_MIME_TYPES, true);
    }

    public function isVideo(): bool
    {
        return in_array($this, self::VIDEO_MIME_TYPES, true);
    }
}
