<?php

declare(strict_types=1);

use SevereHeadache\AuthService\Application\Settings\Settings;
use SevereHeadache\AuthService\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
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
                        'host' => env('DB_HOST', 'localhost'),
                        'port' => env('DB_PORT', 5432),
                        'dbname' => env('DB_NAME', 'name'),
                        'user' => env('DB_USER', 'user'),
                        'password' => env('DB_PASS', 'pass'),
                        'charset' => 'utf-8'
                    ],
                ],
                'app' => [
                    'name' => env('APP_NAME', 'authservice'),
                    'token_lifetime' => env('TOKEN_LIFETIME', 1800),
                    'token_issuer' => env('TOKEN_ISSUER', 'authservice'),
                    'key' => env('SECRET_KEY', 'secret'),
                ],
            ]);
        },
        Configuration::class => function () {
            $configuration = Configuration::forSymmetricSigner(
                new Sha256(),
                InMemory::plainText(env('SECRET_KEY', 'secret')),
            );
            $configuration->setValidationConstraints(
                new SignedWith($configuration->signer(), $configuration->verificationKey()),
                new IssuedBy(env('TOKEN_ISSUER', 'authservice')),
                new ValidAt(new SystemClock(new \DateTimeZone(env('TIMEZONE', 'UTC')))),
            );

            return $configuration;
        }
    ]);
};
