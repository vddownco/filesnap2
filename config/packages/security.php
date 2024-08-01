<?php

declare(strict_types=1);

use App\Infrastructure\Symfony\Security\ApiKeyAuthenticator;
use App\Infrastructure\Symfony\Security\Entity\SecurityUserRole;
use App\Infrastructure\Symfony\Security\UserProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('security', [
        'password_hashers' => [
            PasswordAuthenticatedUserInterface::class => 'auto',
        ],
        'providers' => [
            'use_case_user_provider' => [
                'id' => UserProvider::class,
            ],
        ],
        'firewalls' => [
            'dev' => [
                'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                'security' => false,
            ],
            'api' => [
                'pattern' => '^/api/',
                'lazy' => true,
                'stateless' => true,
                'custom_authenticators' => [
                    ApiKeyAuthenticator::class,
                ],
            ],
            'client' => [
                'lazy' => true,
                'provider' => 'use_case_user_provider',
                'form_login' => [
                    'login_path' => 'client_login',
                    'check_path' => 'client_login',
                    'enable_csrf' => true,
                ],
                'logout' => [
                    'path' => '/logout',
                    'target' => 'client_login',
                ],
                'login_throttling' => [
                    'max_attempts' => 5,
                    'interval' => '5 minutes',
                ],
            ],
        ],
        'access_control' => [
            [
                'path' => '^/u',
                'roles' => SecurityUserRole::User->value,
            ],
            [
                'path' => '^/admin',
                'roles' => SecurityUserRole::Admin->value,
            ],
        ],
    ]);

    if ($containerConfigurator->env() === 'test') {
        $containerConfigurator->extension('security', [
            'password_hashers' => [
                PasswordAuthenticatedUserInterface::class => [
                    'algorithm' => 'auto',
                    'cost' => 4,
                    'time_cost' => 3,
                    'memory_cost' => 10,
                ],
            ],
        ]);
    }
};
