<?php

namespace App\Core;

class View
{
    private static string $viewsPath = '';

    public static function init(): void
    {
        self::$viewsPath = dirname(__DIR__) . '/Views';
    }


    public static function render(string $view, array $data = [], string $layout = 'default'): void
    {
        if (empty(self::$viewsPath)) self::init();

        $file = self::$viewsPath . '/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("View não encontrada: {$view} ({$file})");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        $content = ob_get_clean();

        $layoutFile = match($layout) {
            'painel' => 'layout-painel.php',
            'auth'   => 'layout-auth.php',
            default  => 'layout.php',
        };

        $layoutPath = self::$viewsPath . '/shared/' . $layoutFile;
        extract($data, EXTR_SKIP);
        require $layoutPath;
    }

    public static function partial(string $partial, array $data = []): void
    {
        if (empty(self::$viewsPath)) self::init();

        $file = self::$viewsPath . '/shared/' . $partial . '.php';
        if (!file_exists($file)) return;

        extract($data, EXTR_SKIP);
        require $file;
    }
}