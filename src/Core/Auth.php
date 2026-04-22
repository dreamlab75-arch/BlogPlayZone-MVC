<?php

namespace App\Core;

class Auth
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function check(): bool
    {
        return isset($_SESSION['usuario_id']);
    }

    public static function id(): ?int
    {
        return isset($_SESSION['usuario_id']) ? (int) $_SESSION['usuario_id'] : null;
    }

    public static function nome(): string
    {
        return $_SESSION['usuario_nome'] ?? '';
    }

    public static function perfil(): string
    {
        return $_SESSION['usuario_perfil'] ?? '';
    }

    public static function perfilId(): int
    {
        return (int) ($_SESSION['usuario_perfil_id'] ?? 2);
    }

    public static function avatar(): string
    {
        return $_SESSION['usuario_avatar'] ?? '';
    }

    public static function isAdm(): bool
    {
        return self::perfilId() === 1 || self::perfil() === 'adm';
    }

    public static function isJornalista(): bool
    {
        return in_array(self::perfilId(), [1, 3]);
    }

    public static function exigirLogin(string $redirect = '/auth/login'): void
    {
        if (!self::check()) {
            Router::redirect($redirect . '?erro=' . urlencode('Faça login para continuar.'));
        }
    }

    public static function exigirAdm(): void
    {
        self::exigirLogin();
        if (!self::isAdm()) {
            Router::redirect('/?erro=' . urlencode('Acesso restrito.'));
        }
    }

    public static function login(array $usuario): void
    {
        self::start();
        $_SESSION['usuario_id']        = $usuario['id'];
        $_SESSION['usuario_nome']      = $usuario['nome'];
        $_SESSION['usuario_perfil']    = $usuario['perfil_tipo'] ?? $usuario['perfil'] ?? 'leitor';
        $_SESSION['usuario_perfil_id'] = $usuario['perfil_id'];
        $_SESSION['usuario_avatar']    = $usuario['avatar'] ?? '';
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function atualizarSessao(array $dados): void
    {
        if (isset($dados['nome']))   $_SESSION['usuario_nome']   = $dados['nome'];
        if (isset($dados['avatar'])) $_SESSION['usuario_avatar'] = $dados['avatar'];
    }
}
