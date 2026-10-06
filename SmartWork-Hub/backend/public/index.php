<?php
declare(strict_types=1);

use Dotenv\Dotenv;
use SmartWorkHub\Core\Response;
use SmartWorkHub\Core\Router;
use SmartWorkHub\Routes\Api;

define('PROJECT_ROOT', dirname(__DIR__, 2));
require PROJECT_ROOT . '/vendor/autoload.php';

Dotenv::createImmutable(PROJECT_ROOT)->safeLoad();

$allowedOrigin = $_ENV['CORS_ORIGIN'] ?? '';
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($requestOrigin !== '' && $requestOrigin === $allowedOrigin) {
    header('Access-Control-Allow-Origin: ' . $allowedOrigin);
    header('Vary: Origin');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $router = new Router();
    Api::register($router);
    $router->dispatch(
        $_SERVER['REQUEST_METHOD'] ?? 'GET',
        parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'
    );
} catch (Throwable $exception) {
    error_log((string) $exception);
    Response::json(['error' => 'Internal server error'], 500);
}
