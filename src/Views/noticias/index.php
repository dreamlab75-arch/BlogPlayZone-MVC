<?php
use App\Core\Auth;
use App\Helpers\Upload;
use App\Models\Noticia;

$titulo = 'Notícias';

function qsNoticias(string $categoria, string $busca): string {
    $p = [];
    if ($categoria) $p[] = 'categoria=' . urlencode($categoria);
    if ($busca)     $p[] = 'busca='     . urlencode($busca);
    return $p ? implode('&', $p) . '&' : '';
}
$qs = qsNoticias($categoria, $busca);
?>

<div class="noticias-hero">
  <div class="container d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <h1><i class="bi bi-newspaper me-2"></i>Notícias</h1>
      <p class="mb-0">Fique por dentro de tudo que acontece no mundo dos games</p>
    </div>
    <?php if ($podeEscrever): ?>
      <a href="/noticias/escrever" class="btn-criar-post">
        <i class="bi bi-plus-circle-fill"></i> Escrever Notícia
      </a>
    <?php endif; ?>
  </div>
</div>

<div class="container noticias-page-wrap">
  <a href="/" class="btn-voltar"><i class="bi bi-arrow-left"></i> Voltar ao início</a>

  <?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success">Operação realizada com sucesso!</div>
  <?php endif; ?>
  <?php if (isset($_GET['erro'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
  <?php endif; ?>

  <div class="row g-4">

    <div class="col-lg-8">

      <form method="GET" action="/noticias" class="filtro-bar">
        <div class="row g-2 align-items-center">
          <div class="col-md-6">
            <div class="position-relative">
              <input type="text" name="busca" class="form-control"
                     placeholder="Buscar notícias..."
                     value="<?= htmlspecialchars($busca) ?>"
                     style="padding-left:38px;">
              <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#aaa;font-size:.9rem;pointer-events:none;"></i>
            </div>
          </div>
          <div class="col-md-4">
            <select name="categoria" class="form-select">
              <option value="">Todas as categorias</option>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= $categoria===$cat?'selected':'' ?>>
                  <?= htmlspecialchars(ucfirst($cat)) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn-filtrar w-100">
              <i class="bi bi-funnel me-1"></i>Filtrar
            </button>
          </div>
        </div>
        <?php if ($categoria || $busca): ?>
        <div class="tags-selecionadas-bar mt-3">
          <?php if ($categoria): ?>
            <span class="tag-ativa-pill">
              <?= htmlspecialchars(ucfirst($categoria)) ?>
              <button type="button" onclick="limparFiltro('categoria')">✕</button>
            </span>
          <?php endif; ?>
          <?php if ($busca): ?>
            <span class="tag-ativa-pill">
              "<?= htmlspecialchars($busca) ?>"
              <button type="button" onclick="limparFiltro('busca')">✕</button>
            </span>
          <?php endif; ?>
          <a href="/noticias" style="font-size:.8rem;color:#aaa;align-self:center;text-decoration:none;">Limpar tudo</a>
        </div>
        <?php endif; ?>
      </form>

      <div class="resultado-info">
        <strong><?= $total ?></strong> notícia<?= $total!=1?'s':'' ?>
        <?= $categoria ? ' em <strong>'.htmlspecialchars(ucfirst($categoria)).'</strong>' : '' ?>
        · Página <strong><?= $pagina ?></strong> de <strong><?= $totalPaginas ?></strong>
      </div>

      <?php if (empty($noticias)): ?>
        <div class="empty-state">
          <i class="bi bi-newspaper"></i>
          <p>Nenhuma notícia encontrada.</p>
        </div>
      <?php else: ?>
        <?php foreach ($noticias as $n): ?>
        <a href="/noticias/<?= $n['id'] ?>" class="noticias-card-link">
          <article class="noticias-lista-card">
            <?php if ($n['imagem']): ?>
            <div class="noticias-lista-thumb"
                 style="background-image:url('<?= htmlspecialchars(Upload::url($n['imagem'])) ?>')"></div>
            <?php endif; ?>
            <div class="noticias-lista-corpo">
              <div class="mb-1">
                <span class="badge <?= Noticia::categoriaBadge($n['categoria']) ?>">
                  <?= strtoupper($n['categoria']) ?>
                </span>
              </div>
              <h2 class="noticias-lista-titulo"><?= htmlspecialchars($n['titulo']) ?></h2>
              <p class="noticias-lista-resumo"><?= htmlspecialchars($n['resumo']) ?></p>
              <div class="noticias-lista-meta">
                <img src="<?= htmlspecialchars(Upload::url($n['autor_avatar'] ?? '', '/img/avatar-default.png')) ?>"
                     class="noticias-meta-avatar"
                     <?= Upload::onerror('/img/avatar-default.png') ?>
                     alt="<?= htmlspecialchars($n['autor_nome']) ?>">
                <span class="noticias-meta-autor"><?= htmlspecialchars($n['autor_nome']) ?></span>
                <span class="noticias-meta-sep">·</span>
                <span class="tempo-relativo" data-publicacao="<?= $n['data_publicacao'] ?>">
                  <?= $n['data_publicacao'] ?>
                </span>
                <span class="noticias-meta-sep">·</span>
                <span><i class="bi bi-eye"></i> <?= $n['visualizacoes'] ?></span>
                <span class="noticias-meta-sep">·</span>
                <span><i class="bi bi-heart-fill" style="color:#e74c3c;"></i> <?= $n['curtidas'] ?></span>
                <span class="noticias-meta-sep">·</span>
                <span><i class="bi bi-chat-fill" style="color:#611DF2;"></i> <?= $n['comentarios'] ?></span>
              </div>
            </div>
          </article>
        </a>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if ($totalPaginas > 1): ?>
      <nav class="paginacao">
        <?php if ($pagina > 1): ?>
          <a href="/noticias?<?= $qs ?>page=<?= $pagina-1 ?>" class="nav-pag"><i class="bi bi-chevron-left"></i> Anterior</a>
        <?php endif; ?>
        <?php
        $ini = max(1, $pagina-2); $fim = min($totalPaginas, $pagina+2);
        if ($ini > 1) { echo '<a href="/noticias?' . $qs . 'page=1">1</a>'; if ($ini > 2) echo '<span class="reticencias">…</span>'; }
        for ($p = $ini; $p <= $fim; $p++):
        ?><a href="/noticias?<?= $qs ?>page=<?= $p ?>" class="<?= $p===$pagina?'pag-ativa':'' ?>"><?= $p ?></a><?php
        endfor;
        if ($fim < $totalPaginas) { if ($fim < $totalPaginas-1) echo '<span class="reticencias">…</span>'; echo '<a href="/noticias?' . $qs . 'page=' . $totalPaginas . '">' . $totalPaginas . '</a>'; }
        ?>
        <?php if ($pagina < $totalPaginas): ?>
          <a href="/noticias?<?= $qs ?>page=<?= $pagina+1 ?>" class="nav-pag">Próximo <i class="bi bi-chevron-right"></i></a>
        <?php endif; ?>
      </nav>
      <?php endif; ?>

    </div>

    <div class="col-lg-4">
      <div class="news-sidebar" style="top:90px;">
        <h4><i class="bi bi-fire me-2"></i>Mais Lidas</h4>
        <?php
        $maisLidas = \App\Models\Noticia::maisLidas(5);
        $rank = 1;
        foreach ($maisLidas as $ml):
        ?>
        <div class="noticia-card">
          <div style="display:flex;gap:12px;align-items:flex-start;">
            <span class="noticias-rank"><?= $rank++ ?></span>
            <div>
              <span class="badge <?= Noticia::categoriaBadge($ml['categoria']) ?>" style="font-size:.65rem;margin-bottom:4px;">
                <?= strtoupper($ml['categoria']) ?>
              </span>
              <div class="news-item-title" style="font-size:.88rem;">
                <a href="/noticias/<?= $ml['id'] ?>" style="color:#333;text-decoration:none;">
                  <?= htmlspecialchars($ml['titulo']) ?>
                </a>
              </div>
              <div class="news-item-date mt-1">
                <i class="bi bi-eye"></i> <?= $ml['visualizacoes'] ?> views
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<script>
function limparFiltro(campo) {
  const el = document.querySelector('[name="' + campo + '"]');
  if (el) el.value = '';
  document.querySelector('form').submit();
}
document.querySelectorAll('.alert').forEach(el => {
  setTimeout(() => { el.style.opacity='0'; setTimeout(()=>el.remove(),500); }, 3500);
});
</script>
