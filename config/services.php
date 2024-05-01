<?php

declare(strict_types=1);

use App\Application\Domain\Entity\Snap\FileStorage\FileStorageInterface;
use App\Application\Domain\Entity\Snap\Repository\SnapRepositoryInterface;
use App\Application\Domain\Entity\User\Repository\UserRepositoryInterface;
use App\Application\Domain\Entity\User\Service\PasswordHasherInterface;
use App\Infrastructure\Entity\Snap\FileStorage\LocalFileStorage;
use App\Infrastructure\Entity\Snap\Repository\MariadbSnapRepository;
use App\Infrastructure\Entity\User\Repository\MariadbUserRepository;
use App\Infrastructure\Entity\User\Service\SymfonyPasswordHasher;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('app.environment', '%kernel.environment%');
    $parameters->set('app.project_directory', '%kernel.project_dir%');
    $parameters->set('app.public_directory', '%kernel.project_dir%/public');
    $parameters->set('app.upload.relative_directory', '/user_uploads');
    $parameters->set('app.upload.bytes_max_filesize', 50000000);

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\', __DIR__ . '/../src/')
        ->exclude([
            __DIR__ . '/../src/Kernel.php',
        ]);

    $services->alias(UserRepositoryInterface::class, MariadbUserRepository::class);
    $services->alias(SnapRepositoryInterface::class, MariadbSnapRepository::class);
    $services->alias(FileStorageInterface::class, LocalFileStorage::class);
    $services->alias(PasswordHasherInterface::class, SymfonyPasswordHasher::class);
};
