<?php

namespace App\Controllers;

use App\Core\{Auth, Router, View};
use App\Helpers\Upload;
use App\Models\{Post, Noticia, Tag, Usuario};

class UsuarioController
{
    public function index(): void
    {
        Auth::exigirLogin('/auth/login');

        $usuario = Usuario::buscarPorId(Auth::id());
        if (!$usuario) {
            Auth::logout();
            Router::redirect('/auth/login');
        }

        $aba          = $_GET['aba'] ?? 'posts';
        $posts        = Post::doUsuario(Auth::id());
        $todasTags    = Tag::todas();
        $podeNoticias = Auth::isJornalista();
        $noticias     = $podeNoticias ? Noticia::doUsuario(Auth::id()) : [];

        View::render('painel/index', compact(
            'usuario', 'aba', 'posts', 'todasTags', 'podeNoticias', 'noticias'
        ), true);
    }

    public function editarConta(): void
    {
        Auth::exigirLogin('/auth/login');

        $nome  = trim($_POST['nome']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $bio   = trim($_POST['bio']   ?? '');

        if (strlen($nome) < 2) {
            Router::redirect('/painel?aba=conta&erro=' . urlencode('Nome muito curto.'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Router::redirect('/painel?aba=conta&erro=' . urlencode('E-mail inválido.'));
        }
        if (Usuario::emailExiste($email, Auth::id())) {
            Router::redirect('/painel?aba=conta&erro=' . urlencode('Este e-mail já está em uso.'));
        }

        try {
            $atual  = Usuario::buscarPorId(Auth::id());
            $avatar = Upload::salvar('avatar', 'avatar', Auth::id(), 'avatares')
                      ?? $atual['avatar'];

            Usuario::editar(Auth::id(), $nome, $email, $avatar, $bio ?: null);
            Auth::atualizarSessao(['nome' => $nome, 'avatar' => $avatar]);

            Router::redirect('/painel?aba=conta&sucesso=' . urlencode('Conta atualizada com sucesso!'));
        } catch (\RuntimeException $e) {
            Router::redirect('/painel?aba=conta&erro=' . urlencode($e->getMessage()));
        }
    }

    public function trocarSenha(): void
    {
        Auth::exigirLogin('/auth/login');

        $senhaAtual    = $_POST['senha_atual']    ?? '';
        $senhaNova     = $_POST['senha_nova']     ?? '';
        $senhaConfirma = $_POST['senha_confirma'] ?? '';

        if (!Usuario::verificarSenha(Auth::id(), hash('sha256', $senhaAtual))) {
            Router::redirect('/painel?aba=conta&erro=' . urlencode('Senha atual incorreta.'));
        }
        if (strlen($senhaNova) < 6) {
            Router::redirect('/painel?aba=conta&erro=' . urlencode('A nova senha deve ter no mínimo 6 caracteres.'));
        }
        if ($senhaNova !== $senhaConfirma) {
            Router::redirect('/painel?aba=conta&erro=' . urlencode('As senhas não coincidem.'));
        }

        Usuario::trocarSenha(Auth::id(), hash('sha256', $senhaNova));
        Router::redirect('/painel?aba=conta&sucesso=' . urlencode('Senha atualizada com sucesso!'));
    }
}
