<?php
// AuthController.php
namespace App\Controllers;

use App\Core\{Auth, Router, View};
use App\Helpers\Upload;
use App\Models\Usuario;

class AuthController
{
    public function loginForm(): void
    {
        if (Auth::check()) Router::redirect('/');
        View::render('auth/login', [], 'auth');
    }

    public function login(): void
    {
        $login = trim($_POST['login'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (empty($login) || empty($senha)) {
            Router::redirect('/auth/login?erro=' . urlencode('Preencha todos os campos.'));
        }

        $usuario = Usuario::buscarPorLogin($login, hash('sha256', $senha));

        if (!$usuario) {
            Router::redirect('/auth/login?erro=' . urlencode('Email ou senha incorretos.'));
        }

        Auth::login($usuario);
        Router::redirect('/');
    }

    public function cadastroForm(): void
    {
        if (Auth::check()) Router::redirect('/');
        View::render('auth/cadastro', [], 'auth');
    }

    public function cadastro(): void
    {
        $nome  = trim($_POST['nome']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (strlen($nome) < 2) {
            Router::redirect('/auth/cadastro?erro=' . urlencode('Nome muito curto.'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Router::redirect('/auth/cadastro?erro=' . urlencode('E-mail inválido.'));
        }
        if (strlen($senha) < 6) {
            Router::redirect('/auth/cadastro?erro=' . urlencode('A senha deve ter no mínimo 6 caracteres.'));
        }
        if (Usuario::emailExiste($email)) {
            Router::redirect('/auth/cadastro?erro=' . urlencode('Este e-mail já está cadastrado.'));
        }

        try {
            $usuarioId = Usuario::criar($nome, $email, hash('sha256', $senha));

            $avatar = Upload::salvar('avatar', 'avatar', $usuarioId, 'avatares');
            if ($avatar) Usuario::atualizarAvatar($usuarioId, $avatar);

            Router::redirect('/auth/login?sucesso=' . urlencode('Cadastro realizado! Faça seu login.'));
        } catch (\RuntimeException $e) {
            Router::redirect('/auth/cadastro?erro=' . urlencode($e->getMessage()));
        }
    }

    public function logout(): void
    {
        Auth::logout();
        Router::redirect('/');
    }
}