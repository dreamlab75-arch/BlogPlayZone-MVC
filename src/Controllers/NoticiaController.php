<?php

namespace App\Controllers;

use App\Core\{Auth, Router, View};
use App\Helpers\Upload;
use App\Models\Noticia;

class NoticiaController
{
    // GET /noticias
    public function index(): void
    {
        $pagina       = max(1, (int) ($_GET['page']      ?? 1));
        $categoria    = $_GET['categoria'] ?? '';
        $busca        = $_GET['busca']     ?? '';
        $limite       = 10;

        $noticias     = Noticia::paginadas($pagina, $limite, $categoria, $busca);
        $total        = Noticia::contar($categoria, $busca);
        $totalPaginas = max(1, ceil($total / $limite));
        $categorias   = Noticia::categorias();
        $podeEscrever = Auth::isJornalista();

        View::render('noticias/index', compact('noticias', 'total', 'pagina', 'totalPaginas', 'categorias', 'categoria', 'busca', 'podeEscrever'));
    }

    // GET /noticias/{id}
    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) Router::redirect('/noticias');

        $noticia = Noticia::buscarPorId($id);
        if (!$noticia) Router::redirect('/noticias');

        if (Auth::check()) {
            Noticia::registrarVisualizacao($id, Auth::id());
            $noticia = Noticia::buscarPorId($id);
        }

        $comentarios   = Noticia::comentarios($id);
        $relacionadas  = Noticia::relacionadas($id, $noticia['categoria']);
        $maisLidas     = Noticia::maisLidas(5, $id);
        $usuarioCurtiu = Auth::check() ? Noticia::usuarioCurtiu($id, Auth::id()) : false;
        $corCat        = Noticia::categoriaCor($noticia['categoria']);

        View::render('noticias/show', compact('noticia', 'comentarios', 'relacionadas', 'maisLidas', 'usuarioCurtiu', 'corCat'));
    }

    // GET /noticias/escrever
    public function form(): void
    {
        Auth::exigirLogin('/auth/login');
        if (!Auth::isJornalista()) Router::redirect('/noticias?erro=' . urlencode('Apenas jornalistas podem publicar notícias.'));

        $categorias = Noticia::categoriasValidas();
        View::render('noticias/form', compact('categorias'));
    }

    // POST /noticias/criar
    public function criar(): void
    {
        Auth::exigirLogin('/auth/login');
        if (!Auth::isJornalista()) Router::redirect('/noticias?erro=' . urlencode('Acesso negado.'));

        $titulo    = trim($_POST['titulo']    ?? '');
        $resumo    = trim($_POST['resumo']    ?? '');
        $conteudo  = trim($_POST['conteudo']  ?? '');
        $categoria = trim($_POST['categoria'] ?? '');

        if (strlen($titulo) < 5)   Router::redirect('/noticias/escrever?erro=' . urlencode('Título muito curto.'));
        if (strlen($resumo) < 10)  Router::redirect('/noticias/escrever?erro=' . urlencode('Resumo muito curto.'));
        if (strlen($conteudo) < 50) Router::redirect('/noticias/escrever?erro=' . urlencode('Conteúdo muito curto.'));
        if (!in_array($categoria, Noticia::categoriasValidas())) {
            Router::redirect('/noticias/escrever?erro=' . urlencode('Categoria inválida.'));
        }

        try {
            $noticiaId = Noticia::criar([
                ':titulo'     => $titulo,
                ':resumo'     => $resumo,
                ':conteudo'   => $conteudo,
                ':imagem'     => '',
                ':categoria'  => $categoria,
                ':usuario_id' => Auth::id(),
            ]);

            $pasta  = dirname(__DIR__, 2) . '/public/uploads/noticias';
            $imagem = Upload::salvar('imagem', 'noticia', $noticiaId, $pasta);
            if ($imagem) Noticia::atualizarImagem($noticiaId, $imagem);

            Router::redirect('/noticias/' . $noticiaId . '?sucesso=1');
        } catch (\RuntimeException $e) {
            Router::redirect('/noticias/escrever?erro=' . urlencode($e->getMessage()));
        }
    }

    // POST /noticias/curtir
    public function curtir(): void
    {
        Auth::exigirLogin('/auth/login');
        $id = (int) ($_POST['noticia_id'] ?? 0);
        if ($id) Noticia::toggleCurtida($id, Auth::id());
        Router::redirect('/noticias/' . $id);
    }

    // POST /noticias/comentar
    public function comentar(): void
    {
        Auth::exigirLogin('/auth/login');
        $id         = (int) ($_POST['noticia_id'] ?? 0);
        $comentario = trim($_POST['comentario'] ?? '');
        if ($id && strlen($comentario) >= 2) {
            Noticia::comentar($id, Auth::id(), $comentario);
        }
        Router::redirect('/noticias/' . $id);
    }

    // POST /noticias/editar
    public function editar(): void
    {
        Auth::exigirLogin('/auth/login');

        $noticiaId = (int) ($_POST['noticia_id'] ?? 0);
        $titulo    = trim($_POST['titulo']        ?? '');
        $resumo    = trim($_POST['resumo']        ?? '');
        $conteudo  = trim($_POST['conteudo']      ?? '');
        $categoria = trim($_POST['categoria']     ?? '');

        if (!$noticiaId || strlen($titulo) < 5 || !in_array($categoria, Noticia::categoriasValidas())) {
            Router::redirect('/painel?aba=noticias&erro=' . urlencode('Dados inválidos.'));
        }

        if (!Noticia::pertenceAo($noticiaId, Auth::id()) && !Auth::isAdm()) {
            Router::redirect('/painel?aba=noticias&erro=' . urlencode('Sem permissão.'));
        }

        try {
            $noticiaAtual = Noticia::buscarPorId($noticiaId);
            $pasta        = dirname(__DIR__, 2) . '/public/uploads/noticias';
            $imagem       = Upload::salvar('imagem', 'noticia', $noticiaId, $pasta) ?? $noticiaAtual['imagem'];

            Noticia::editar($noticiaId, [
                ':titulo'    => $titulo,
                ':resumo'    => $resumo,
                ':conteudo'  => $conteudo,
                ':imagem'    => $imagem,
                ':categoria' => $categoria,
            ]);

            Router::redirect('/painel?aba=noticias&sucesso=' . urlencode('Notícia atualizada com sucesso.'));
        } catch (\RuntimeException $e) {
            Router::redirect('/painel?aba=noticias&erro=' . urlencode($e->getMessage()));
        }
    }

    // GET /noticias/deletar?id=X
    public function deletar(): void
    {
        Auth::exigirLogin('/auth/login');
        $noticiaId = (int) ($_GET['id'] ?? 0);
        if (!$noticiaId) Router::redirect('/painel?aba=noticias&erro=' . urlencode('Notícia inválida.'));

        if (!Noticia::pertenceAo($noticiaId, Auth::id()) && !Auth::isAdm()) {
            Router::redirect('/painel?aba=noticias&erro=' . urlencode('Sem permissão.'));
        }

        try {
            Noticia::deletar($noticiaId);
            Router::redirect('/painel?aba=noticias&sucesso=' . urlencode('Notícia deletada com sucesso.'));
        } catch (\Exception $e) {
            Router::redirect('/painel?aba=noticias&erro=' . urlencode('Erro ao deletar a notícia.'));
        }
    }
}
