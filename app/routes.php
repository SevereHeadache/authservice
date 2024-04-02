<?php

declare(strict_types=1);

use SevereHeadache\AuthService\Application\Controllers\AuthController;
use Slim\App;

return function (App $app) {
    $app->get('/', [AuthController::class, 'index']);
    $app->get('/authenticate', [AuthController::class, 'form']);
    $app->post('/authenticate', [AuthController::class, 'authenticate']);
};
