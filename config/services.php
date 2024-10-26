<?php

declare(strict_types=1);

use App\Infrastructure\Symfony\Service\FormatConverter\Converter\ConvertFormat;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\FormatStorageInterface;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\LocalStorage;
use App\Infrastructure\Symfony\Service\FormatConverter\Converter\Thumbnail\ThumbnailLocalStorage;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('app.environment', '%kernel.environment%');
    $parameters->set('app.project_directory', '%kernel.project_dir%');
    $parameters->set('app.public_directory', '%kernel.project_dir%/public');
    $parameters->set('app.upload_directory', '%kernel.project_dir%/user_uploads');
    $parameters->set('app.converted_upload_directory', '%kernel.project_dir%/user_converted_uploads');
    $parameters->set('app.thumbnail_directory', '%kernel.project_dir%/public/snap');
    $parameters->set('app.upload.bytes_max_filesize', 50000000);

    $services = $containerConfigurator->services();

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->load('App\\', sprintf('%s/../src/', __DIR__))
        ->exclude([
            sprintf('%s/../src/Kernel.php', __DIR__),
        ]);

    $services->instanceof(FormatStorageInterface::class)->tag('format.storage.interface');
    $services->set('thumbnail.local.storage', ThumbnailLocalStorage::class);
    $services->set('webp.local.storage', LocalStorage::class)->arg('$format', ConvertFormat::Webp);
    $services->set('avif.local.storage', LocalStorage::class)->arg('$format', ConvertFormat::Avif);
    $services->set('webm.local.storage', LocalStorage::class)->arg('$format', ConvertFormat::Webm);
};
