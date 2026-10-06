<?php
declare(strict_types=1);

namespace SmartWorkHub\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as $route) {
            if ($route['path'] === $path && $route['method'] === $method) {
                ($route['handler'])();
                return;
            }
        }

        foreach ($this->routes as $route) {
            if ($route['path'] === $path) {
                Response::json(['error' => 'Method not allowed'], 405);
            }
        }

        Response::json(['error' => 'Not found'], 404);
    }

    private function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
    }
}
