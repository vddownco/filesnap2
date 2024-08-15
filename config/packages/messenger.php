<?php

declare(strict_types=1);

use App\Infrastructure\Symfony\Message\ConversionMessage;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'messenger' => [
            'transports' => [
                'async' => '%env(MESSENGER_TRANSPORT_DSN)%',
            ],
            'routing' => [
                ConversionMessage::class => 'async',
            ],
        ],
    ]);
};
