<?php

namespace App\Controllers;

use App\Core\{Auth, Router, View};
use App\Helpers\Upload;
use App\Models\{Post, Tag};

class PostController
{
    public function index(): void
    {
        $pagina     = max(1, (int) ($_GET['page']  ?? 1));
        $busca      = $_GET['busca']  ?? '';
        $tagsFiltro = $_GET['tags']   ?? [];
        $ordem      = $_GET['ordem']  ?? 'recentes';
        $limite     = 10;

        $posts        = Post::paginados($pagina, $limite, $ordem, $busca, $tagsFiltro);
        $total        = Post::contar($busca, $tagsFiltro);
        $totalPaginas = max(1, ceil($total / $limite));
        $tags         = Tag::todas();

        View::render('posts/index', compact(
            'posts', 'total', 'pagina', 'totalPaginas',
            'tags', 'busca', 'tagsFiltro', 'ordem'
        ));
    }

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) Router::redirect('/posts');

        $post = Post::buscarPorId($id);
        if (!$post) Router::redirect('/posts');

        if (Auth::check()) {
            Post::registrarVisualizacao($id, Auth::id());
            $post = Post::buscarPorId($id);
        }

        $tags          = $post['tags'] ? explode(',', $post['tags']) : [];
        $comentarios   = Post::comentarios($id);
        $usuarioCurtiu = Auth::check() ? Post::usuarioCurtiu($id, Auth::id()) : false;

        View::render('posts/show', compact('post', 'tags', 'comentarios', 'usuarioCurtiu'));
    }

    public function criar(): void
    {
        Auth::exigirLogin('/auth/login');

        $titulo   = trim($_POST['titulo']   ?? '');
        $conteudo = trim($_POST['conteudo'] ?? '');
        $tagIds   = array_slice(array_map('intval', $_POST['tags_post'] ?? []), 0, 5);

        if (strlen($titulo) < 5) {
            Router::redirect('/posts?erro=' . urlencode('Título muito curto (mínimo 5 caracteres).'));
        }
        if (strlen($conteudo) < 50) {
            Router::redirect('/posts?erro=' . urlencode('Conteúdo muito curto (mínimo 50 caracteres).'));
        }

        try {
            $postId = Post::criar($titulo, $conteudo, Auth::id());

            $imagem = Upload::salvar('imagem', 'post', $postId, 'posts');
            if ($imagem) Post::atualizarImagem($postId, $imagem);

            Post::sincronizarTags($postId, $tagIds);

            Router::redirect('/posts?sucesso=1');
        } catch (\RuntimeException $e) {
            Router::redirect('/posts?erro=' . urlencode($e->getMessage()));
        }
    }

    public function curtir(): void
    {
        Auth::exigirLogin('/auth/login');
        $id = (int) ($_POST['post_id'] ?? 0);
        if ($id) Post::toggleCurtida($id, Auth::id());
        Router::redirect('/posts/' . $id);
    }

    public function comentar(): void
    {
        Auth::exigirLogin('/auth/login');
        $id         = (int) ($_POST['post_id'] ?? 0);
        $comentario = trim($_POST['comentario'] ?? '');
        if ($id && strlen($comentario) >= 2) {
            Post::comentar($id, Auth::id(), $comentario);
        }
        Router::redirect('/posts/' . $id);
    }

    public function editar(): void
    {
        Auth::exigirLogin('/auth/login');

        $postId   = (int) ($_POST['post_id'] ?? 0);
        $titulo   = trim($_POST['titulo']    ?? '');
        $conteudo = trim($_POST['conteudo']  ?? '');
        $tagIds   = array_slice(array_map('intval', $_POST['tags_post'] ?? []), 0, 5);

        if (!$postId || strlen($titulo) < 5 || strlen($conteudo) < 50) {
            Router::redirect('/painel?erro=' . urlencode('Dados inválidos.'));
        }

        if (!Post::pertenceAo($postId, Auth::id()) && !Auth::isAdm()) {
            Router::redirect('/painel?erro=' . urlencode('Sem permissão.'));
        }

        try {
            $postAtual = Post::buscarPorId($postId);
            $imagem    = Upload::salvar('imagem', 'post', $postId, 'posts') ?? $postAtual['imagem'];

            Post::editar($postId, $titulo, $conteudo, $imagem);
            Post::sincronizarTags($postId, $tagIds);

            Router::redirect('/painel?sucesso=' . urlencode('Post atualizado com sucesso!'));
        } catch (\RuntimeException $e) {
            Router::redirect('/painel?erro=' . urlencode($e->getMessage()));
        }
    }

    public function deletar(): void
    {
        Auth::exigirLogin('/auth/login');
        $postId = (int) ($_GET['id'] ?? 0);
        if (!$postId) Router::redirect('/painel?erro=' . urlencode('Post inválido.'));

        if (!Post::pertenceAo($postId, Auth::id()) && !Auth::isAdm()) {
            Router::redirect('/painel?erro=' . urlencode('Sem permissão.'));
        }

        try {
            Post::deletar($postId);
            Router::redirect('/painel?sucesso=' . urlencode('Post deletado com sucesso.'));
        } catch (\Exception) {
            Router::redirect('/painel?erro=' . urlencode('Erro ao deletar o post.'));
        }
    }
}
