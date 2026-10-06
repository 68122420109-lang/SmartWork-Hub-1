<?php
declare(strict_types=1);

namespace SmartWorkHub\Routes;

use SmartWorkHub\Controllers\AuthController;
use SmartWorkHub\Controllers\HealthController;
use SmartWorkHub\Core\Router;

final class Api
{
    public static function register(Router $router): void
    {
        $router->get('/api/health', [HealthController::class, 'show']);
        $router->post('/api/login', [AuthController::class, 'login']);
        $router->get('/api/me', [AuthController::class, 'me']);
        $router->post('/api/logout', [AuthController::class, 'logout']);
    }
}
