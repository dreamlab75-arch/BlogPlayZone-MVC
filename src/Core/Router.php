<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    // ── Registro de rotas ────────────────────────────────────────────────────

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    // ── Resolução ────────────────────────────────────────────────────────────

    public function dispatch(string $method, string $uri): void
    {
        // Remove query string da URI
        $path = strtok($uri, '?');
        // Remove barra final (exceto raiz)
        $path = ($path !== '/') ? rtrim($path, '/') : $path;

        $routes = $this->routes[$method] ?? [];

        // Correspondência exata
        if (isset($routes[$path])) {
            $this->call($routes[$path]);
            return;
        }

        // Correspondência com parâmetros (ex: /posts/{id})
        foreach ($routes as $route => $handler) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // remove full match

                // Extrai nomes dos parâmetros
                preg_match_all('/\{([^}]+)\}/', $route, $paramNames);
                foreach ($paramNames[1] as $i => $name) {
                    $_GET[$name] = $matches[$i];
                }

                $this->call($handler);
                return;
            }
        }

        // 404
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

    // ── Utilitários estáticos ────────────────────────────────────────────────

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
