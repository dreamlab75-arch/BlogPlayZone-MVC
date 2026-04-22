<?php

namespace App\Core;

class View
{
    private static string $viewsPath = '';

    public static function init(): void
    {
        self::$viewsPath = dirname(__DIR__) . '/Views';
    }

    /**
     * Renderiza uma view dentro do layout padrão.
     *
     * @param string $view  Ex: 'posts/index', 'auth/login'
     * @param array  $data  Variáveis disponíveis na view e no layout
     */
    public static function render(string $view, array $data = []): void
    {
        if (empty(self::$viewsPath)) self::init();

        $file = self::$viewsPath . '/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("View não encontrada: {$view} ({$file})");
        }

        // Captura o HTML da view em buffer
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        $content = ob_get_clean();

        // Renderiza dentro do layout
        $layout = self::$viewsPath . '/shared/layout.php';
        extract($data, EXTR_SKIP); // re-extrai para o layout também ter as variáveis
        require $layout;
    }

    /**
     * Renderiza sem layout (para fragmentos incluídos por outras views).
     */
    public static function partial(string $partial, array $data = []): void
    {
        if (empty(self::$viewsPath)) self::init();

        $file = self::$viewsPath . '/shared/' . $partial . '.php';
        if (!file_exists($file)) return;

        extract($data, EXTR_SKIP);
        require $file;
    }
}
