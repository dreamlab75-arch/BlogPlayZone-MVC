<?php

namespace App\Core;

class Router
{
    private array $routes = [];


    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }


    public function dispatch(string $method, string $uri): void
    {
        $path = strtok($uri, '?');
        $path = ($path !== '/') ? rtrim($path, '/') : $path;

        $routes = $this->routes[$method] ?? [];

        if (isset($routes[$path])) {
            $this->call($routes[$path]);
            return;
        }

        foreach ($routes as $route => $handler) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); 

                preg_match_all('/\{([^}]+)\}/', $route, $paramNames);
                foreach ($paramNames[1] as $i => $name) {
                    $_GET[$name] = $matches[$i];
                }

                $this->call($handler);
                return;
            }
        }

        http_response_code(404);
        View::render('pages/404');
    }

    private function call(array $handler): void
    {
        [$controllerClass, $method] = $handler;

        $fullClass = "App\\Controllers\\{$controllerClass}";

        if (!class_exists($fullClass)) {
            throw new \RuntimeException("Controller não encontrado: {$fullClass}");
        }

        $controller = new $fullClass();

        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Método não encontrado: {$fullClass}::{$method}");
        }

        $controller->$method();
    }

    public static function redirect(string $url, int $code = 303): never
    {
        header("Location: {$url}", true, $code);
        exit;
    }

    public static function back(string $fallback = '/'): never
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? $fallback;
        self::redirect($ref);
    }
}
