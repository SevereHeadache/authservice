<?php

declare(strict_types=1);

use DI\ContainerBuilder;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/Helpers/helpers.php';

define('PROJECT_PATH', __DIR__);

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(PROJECT_PATH);
$dotenv->load();

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAttributes(true);

if (env('APP_ENV', 'prod') === 'prod') { // Should be set to true in production
	$containerBuilder->enableCompilation(PROJECT_PATH . '/var/cache');
}

// Set up settings
$settings = require PROJECT_PATH . '/app/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require PROJECT_PATH . '/app/dependencies.php';
$dependencies($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

return $container;
