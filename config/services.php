<?php

declare(strict_types=1);

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
        ->load('App\\', __DIR__ . '/../src/')
        ->exclude([
            __DIR__ . '/../src/Kernel.php',
        ]);
};
