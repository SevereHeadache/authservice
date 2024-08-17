<?php

declare(strict_types=1);

use SevereHeadache\AuthService\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use SevereHeadache\AuthService\Application\Core\AuthInterface;
use SevereHeadache\AuthService\Application\Core\AuthService;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $formatter = new LineFormatter(
                allowInlineLineBreaks: true,
            );

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);

            return $logger;
        },
        EntityManagerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class)->get('doctrine');
            $isDev = $settings['dev_mode'];
            if ($isDev) {
                $queryCache = new ArrayAdapter();
                $metadataCache = new ArrayAdapter();
            } else {
                $queryCache = new PhpFilesAdapter('doctrine_queries');
                $metadataCache = new PhpFilesAdapter('doctrine_metadata');
            }

            $config = new Configuration();
            $config->setMetadataCache($metadataCache);
            $driverImpl = new AttributeDriver($settings['metadata_dirs']);
            $config->setMetadataDriverImpl($driverImpl);
            $config->setQueryCache($queryCache);
            $config->setProxyDir($settings['proxy_dir']);
            $config->setProxyNamespace($settings['proxy_namespace']);
            $config->setAutoGenerateProxyClasses($isDev);

            $connection = DriverManager::getConnection($settings['connection'], $config);

            return new EntityManager($connection, $config);
        },
        AuthInterface::class => function (ContainerInterface $c) {
            return $c->get(AuthService::class);
        }
    ]);
};
