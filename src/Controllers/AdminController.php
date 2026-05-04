<?php

namespace App\Controllers;

use App\Core\{Auth, Router, View};
use App\Helpers\Upload;
use App\Models\{Post, Noticia, Tag, Usuario};

class AdminController
{
    public function index(): void
    {
        Auth::exigirAdm();

        $aba      = $_GET['aba'] ?? 'usuarios';
        $usuarios = Usuario::todos();
        $perfis   = Usuario::perfis();
        $posts    = Post::todos();
        $noticias = Noticia::todas();
        $todasTags = Tag::todas();

        $cats = Noticia::categoriasValidas();

        View::render('admin/index', compact(
            'aba', 'usuarios', 'perfis', 'posts', 'noticias', 'todasTags', 'cats'
        ), 'painel');
    }

    public function adicionarUsuario(): void
    {
        Auth::exigirAdm();

        $nome     = trim($_POST['nome']  ?? '');
        $email    = trim($_POST['email'] ?? '');
        $senha    = $_POST['senha']      ?? '';
        $perfilId = (int) ($_POST['perfil_id'] ?? 2);

        if (strlen($nome) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 6) {
            Router::redirect('/admin?aba=usuarios&erro=' . urlencode('Dados inválidos.'));
        }
        if (Usuario::emailExiste($email)) {
            Router::redirect('/admin?aba=usuarios&erro=' . urlencode('E-mail já cadastrado.'));
        }

        $id = Usuario::criar($nome, $email, hash('sha256', $senha));
        Usuario::atualizarPerfil($id, $perfilId);
        Router::redirect('/admin?aba=usuarios&sucesso=' . urlencode('Usuário criado com sucesso.'));
    }

    public function editarUsuario(): void
    {
        Auth::exigirAdm();

        $usuarioId = (int) ($_POST['usuario_id'] ?? 0);
        $perfilId  = (int) ($_POST['perfil_id']  ?? 2);

        if (!$usuarioId) {
            Router::redirect('/admin?aba=usuarios&erro=' . urlencode('Usuário inválido.'));
        }

        Usuario::atualizarPerfil($usuarioId, $perfilId);
        Router::redirect('/admin?aba=usuarios&sucesso=' . urlencode('Usuário atualizado.'));
    }

    public function apagarUsuario(): void
    {
        Auth::exigirAdm();

        $usuarioId = (int) ($_GET['id'] ?? 0);
        if (!$usuarioId || $usuarioId === Auth::id()) {
            Router::redirect('/admin?aba=usuarios&erro=' . urlencode('Operação inválida.'));
        }

        Usuario::deletar($usuarioId);
        Router::redirect('/admin?aba=usuarios&sucesso=' . urlencode('Usuário removido.'));
    }

    public function editarPost(): void
    {
        Auth::exigirAdm();

        $postId   = (int) ($_POST['post_id']  ?? 0);
        $titulo   = trim($_POST['titulo']     ?? '');
        $conteudo = trim($_POST['conteudo']   ?? '');
        $tagIds   = array_slice(array_map('intval', $_POST['tags_post'] ?? []), 0, 5);

        if (!$postId || strlen($titulo) < 5 || strlen($conteudo) < 50) {
            Router::redirect('/admin?aba=posts&erro=' . urlencode('Dados inválidos.'));
        }

        try {
            $postAtual = Post::buscarPorId($postId);
            $imagem    = Upload::salvar('imagem', 'post', $postId, 'posts') ?? $postAtual['imagem'];

            Post::editar($postId, $titulo, $conteudo, $imagem);
            Post::sincronizarTags($postId, $tagIds);

            Router::redirect('/admin?aba=posts&sucesso=' . urlencode('Post atualizado com sucesso.'));
        } catch (\RuntimeException $e) {
            Router::redirect('/admin?aba=posts&erro=' . urlencode($e->getMessage()));
        }
    }

    public function deletarPost(): void
    {
        Auth::exigirAdm();

        $postId = (int) ($_GET['id'] ?? 0);
        if (!$postId) {
            Router::redirect('/admin?aba=posts&erro=' . urlencode('Post inválido.'));
        }

        try {
            Post::deletar($postId);
            Router::redirect('/admin?aba=posts&sucesso=' . urlencode('Post deletado com sucesso.'));
        } catch (\Exception) {
            Router::redirect('/admin?aba=posts&erro=' . urlencode('Erro ao deletar o post.'));
        }
    }

    public function editarNoticia(): void
    {
        Auth::exigirAdm();

        $noticiaId = (int) ($_POST['noticia_id'] ?? 0);
        $titulo    = trim($_POST['titulo']        ?? '');
        $resumo    = trim($_POST['resumo']        ?? '');
        $conteudo  = trim($_POST['conteudo']      ?? '');
        $categoria = trim($_POST['categoria']     ?? '');

        if (!$noticiaId || strlen($titulo) < 5 || !in_array($categoria, Noticia::categoriasValidas())) {
            Router::redirect('/admin?aba=noticias&erro=' . urlencode('Dados inválidos.'));
        }

        try {
            $noticiaAtual = Noticia::buscarPorId($noticiaId);
            $imagem       = Upload::salvar('imagem', 'noticia', $noticiaId, 'noticias')
                            ?? $noticiaAtual['imagem'];

            Noticia::editar($noticiaId, [
                ':titulo'    => $titulo,
                ':resumo'    => $resumo,
                ':conteudo'  => $conteudo,
                ':imagem'    => $imagem,
                ':categoria' => $categoria,
            ]);

            Router::redirect('/admin?aba=noticias&sucesso=' . urlencode('Notícia atualizada com sucesso.'));
        } catch (\RuntimeException $e) {
            Router::redirect('/admin?aba=noticias&erro=' . urlencode($e->getMessage()));
        }
    }

    public function deletarNoticia(): void
    {
        Auth::exigirAdm();

        $noticiaId = (int) ($_GET['id'] ?? 0);
        if (!$noticiaId) {
            Router::redirect('/admin?aba=noticias&erro=' . urlencode('Notícia inválida.'));
        }

        try {
            Noticia::deletar($noticiaId);
            Router::redirect('/admin?aba=noticias&sucesso=' . urlencode('Notícia deletada com sucesso.'));
        } catch (\Exception) {
            Router::redirect('/admin?aba=noticias&erro=' . urlencode('Erro ao deletar a notícia.'));
        }
    }
}