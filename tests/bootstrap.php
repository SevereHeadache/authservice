<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Helpers/helpers.php';

define('PROJECT_PATH', __DIR__ . '/..');

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(PROJECT_PATH);
$dotenv->load();
