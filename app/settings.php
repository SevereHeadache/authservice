<?php

declare(strict_types=1);

use SevereHeadache\AuthService\Application\Settings\Settings;
use SevereHeadache\AuthService\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => env('APP_ENV', 'prod') !== 'prod',
                'logError' => true,
                'logErrorDetails' => true,
                'logger' => [
                    'name' => 'app',
                    'path' => PROJECT_PATH . '/logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'doctrine' => [
                    'dev_mode' => env('APP_ENV', 'prod') !== 'prod',
                    'proxy_dir' => PROJECT_PATH . '/var/cache/doctrine',
                    'proxy_namespace' => 'SevereHeadache\AuthService\Proxies',
                    'metadata_dirs' => [PROJECT_PATH . '/src/Domain'],
                    'connection' => [
                        'driver' => 'pdo_pgsql',
                        'host' => env('DB_HOST'),
                        'port' => env('DB_PORT', 5432),
                        'dbname' => env('DB_NAME'),
                        'user' => env('DB_USER'),
                        'password' => env('DB_PASS'),
                        'charset' => 'utf-8'
                    ],
                ],
            ]);
        },
        Configuration::class => function () {
            $configuration = Configuration::forSymmetricSigner(
                new Sha256(),
                InMemory::plainText(env('SECRET_KEY')),
            );
            $configuration->setValidationConstraints(
                new SignedWith($configuration->signer(), $configuration->verificationKey()),
                new IssuedBy(env('TOKEN_ISSUER')),
            );

            return $configuration;
        }
    ]);
};
