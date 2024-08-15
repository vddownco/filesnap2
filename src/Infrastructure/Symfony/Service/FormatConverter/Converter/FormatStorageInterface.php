<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Service\FormatConverter\Converter;

use App\Application\Domain\Snap\Snap;
use Symfony\Component\HttpFoundation\File\File;

interface FormatStorageInterface
{
    public function save(Snap $snap, File $file): void;

    public function get(Snap $snap): ?File;

    public function delete(Snap $snap): void;
}
