<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (self::$instance === null) {
            $path = dirname(__DIR__, 2) . '/banco.db';

            try {
                self::$instance = new PDO('sqlite:' . $path);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new \RuntimeException('Erro ao conectar ao banco de dados: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }

    // Impede instanciação e clonagem
    private function __construct() {}
    private function __clone() {}
}
