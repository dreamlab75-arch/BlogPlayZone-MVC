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
     * Renderiza uma view.
     *
     * @param string $view    Ex: 'posts/index', 'painel/index'
     * @param array  $data    Variáveis disponíveis na view
     * @param bool   $semNav  Se true, usa layout sem navbar/footer (para painel e admin)
     */
    public static function render(string $view, array $data = [], bool $semNav = false): void
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

        // Escolhe o layout
        $layoutFile = $semNav ? 'layout-painel.php' : 'layout.php';
        $layout = self::$viewsPath . '/shared/' . $layoutFile;

        extract($data, EXTR_SKIP);
        require $layout;
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
