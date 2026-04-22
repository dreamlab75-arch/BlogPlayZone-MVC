<?php
use App\Core\Auth;
use App\Helpers\Upload;

$titulo = 'Posts';

function tempoPost(string $data): string {
    $agora     = new DateTime('now', new DateTimeZone('UTC'));
    $publicado = new DateTime($data, new DateTimeZone('UTC'));
    $diff      = $agora->diff($publicado);
    $min       = ($diff->days * 1440) + ($diff->h * 60) + $diff->i;
    if ($diff->days >= 365) { $a = floor($diff->days/365); return "Há {$a} ano" . ($a>1?'s':''); }
    if ($diff->days >= 30)  return 'Há ' . $diff->m . ($diff->m>1?' meses':' mês');
    if ($diff->days >= 7)   { $s = floor($diff->days/7); return "Há {$s} semana" . ($s>1?'s':''); }
    if ($diff->days >= 1)   return 'Há ' . $diff->days . ' dia' . ($diff->days>1?'s':'');
    if ($min >= 60)         return 'Há ' . $diff->h . ' hora' . ($diff->h>1?'s':'');
    if ($min >= 1)          return "Há {$min} minuto" . ($min>1?'s':'');
    return 'Agora mesmo';
}

function qsPosts(string $busca, string $ordem, array $tags): string {
    $p = [];
    if ($busca)              $p[] = 'busca=' . urlencode($busca);
    if ($ordem !== 'recentes') $p[] = 'ordem=' . urlencode($ordem);
    foreach ($tags as $t)    $p[] = 'tags[]=' . urlencode($t);
    return $p ? implode('&', $p) . '&' : '';
}
$qs = qsPosts($busca, $ordem, $tagsFiltro);
?>

<!-- HERO -->
<div class="posts-page-hero">
  <div class="container d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <h1><i class="bi bi-controller me-2"></i>Posts da Comunidade</h1>
      <p>Opiniões, reviews e experiências dos gamers</p>
    </div>
    <?php if (Auth::check()): ?>
      <button class="btn-criar-post" onclick="abrirModalCriar()">
        <i class="bi bi-plus-circle-fill"></i> Criar Post
      </button>
    <?php endif; ?>
  </div>
</div>

<div class="container" style="max-width:960px; padding-bottom:60px;">

  <a href="/" class="btn-voltar"><i class="bi bi-arrow-left"></i> Voltar ao início</a>

  <?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success">Post publicado com sucesso!</div>
  <?php endif; ?>
  <?php if (isset($_GET['erro'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
  <?php endif; ?>

  <!-- FILTROS -->
  <form method="GET" action="/posts" id="formFiltro" class="filtro-bar">
    <div class="row g-2 align-items-center">
      <div class="col-md-4">
        <div class="position-relative">
          <input type="text" name="busca" class="form-control" placeholder="Buscar por título..."
                 value="<?= htmlspecialchars($busca) ?>" style="padding-left:38px;">
          <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#aaa;font-size:.9rem;pointer-events:none;"></i>
        </div>
      </div>
      <div class="col-md-3">
        <select name="ordem" class="form-select">
          <option value="recentes" <?= $ordem==='recentes'?'selected':'' ?>>🕒 Mais recentes</option>
          <option value="antigos"  <?= $ordem==='antigos' ?'selected':'' ?>>📅 Mais antigos</option>
          <option value="vistos"   <?= $ordem==='vistos'  ?'selected':'' ?>>👁️ Mais vistos</option>
        </select>
      </div>
      <div class="col-md-3 tags-dropdown-wrap">
        <button type="button" class="btn-tags-toggle w-100 <?= !empty($tagsFiltro)?'ativo':'' ?>"
                onclick="toggleTagsDropdown()" id="btnTagsToggle">
          <i class="bi bi-tags me-1"></i>
          Tags<?= !empty($tagsFiltro) ? ' <span class="badge" style="background:#611DF2;border-radius:10px;font-size:.7rem;">'.count($tagsFiltro).'</span>' : '' ?>
        </button>
        <div class="dropdown-tags-box" id="dropdownTags">
          <?php foreach ($tags as $tag): ?>
            <label class="tag-item">
              <input type="checkbox" name="tags[]" value="<?= htmlspecialchars($tag['nome']) ?>"
                     <?= in_array($tag['nome'], $tagsFiltro) ? 'checked' : '' ?>>
              <?= htmlspecialchars($tag['nome']) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn-filtrar w-100"><i class="bi bi-funnel me-1"></i>Filtrar</button>
      </div>
    </div>

    <?php if (!empty($tagsFiltro)): ?>
    <div class="tags-selecionadas-bar mt-3">
      <?php foreach ($tagsFiltro as $t): ?>
        <span class="tag-ativa-pill">
          <?= htmlspecialchars($t) ?>
          <button type="button" onclick="removerTag('<?= htmlspecialchars($t) ?>')" title="Remover">✕</button>
        </span>
      <?php endforeach; ?>
      <a href="/posts" style="font-size:.8rem;color:#aaa;align-self:center;text-decoration:none;">Limpar tudo</a>
    </div>
    <?php endif; ?>
  </form>

  <div class="resultado-info">
    <strong><?= $total ?></strong> post<?= $total != 1 ? 's' : '' ?>
    · Página <strong><?= $pagina ?></strong> de <strong><?= $totalPaginas ?></strong>
  </div>

  <!-- LISTA -->
  <?php if (empty($posts)): ?>
    <div class="empty-state">
      <i class="bi bi-controller"></i>
      <p>Nenhum post encontrado.</p>
    </div>
  <?php else: ?>
    <?php foreach ($posts as $post):
      $postTags = $post['tags'] ? explode(',', $post['tags']) : [];
    ?>
    <div class="post-card">
      <div class="post-author">
        <div class="post-avatar">
          <img src="<?= htmlspecialchars(Upload::url($post['avatar'] ?? '', '/img/avatar-default.png')) ?>"
               alt="<?= htmlspecialchars($post['autor']) ?>"
               <?= Upload::onerror('/img/avatar-default.png') ?>>
        </div>
        <div class="post-author-info">
          <h6><?= htmlspecialchars($post['autor']) ?></h6>
          <small>
            <i class="bi bi-clock me-1"></i>
            <span class="tempo-relativo" data-publicacao="<?= $post['data_publicacao'] ?>">
              <?= tempoPost($post['data_publicacao']) ?>
            </span>
          </small>
        </div>
      </div>
      <div class="post-tags">
        <?php foreach ($postTags as $t): ?>
          <span class="post-tag"><?= htmlspecialchars(trim($t)) ?></span>
        <?php endforeach; ?>
      </div>
      <h4 class="post-title">
        <a href="/posts/<?= $post['id'] ?>"><?= htmlspecialchars($post['titulo']) ?></a>
      </h4>
      <p class="post-excerpt"><?= htmlspecialchars(mb_substr($post['conteudo'], 0, 180)) ?>...</p>
      <div class="post-footer">
        <div class="post-stats">
          <span><i class="bi bi-heart-fill" style="color:#e74c3c;"></i> <?= $post['curtidas'] ?></span>
          <span><i class="bi bi-chat-fill"></i> <?= $post['comentarios'] ?></span>
          <span><i class="bi bi-eye-fill"></i> <?= $post['visualizacoes'] ?></span>
        </div>
        <a class="btn-ler-post" href="/posts/<?= $post['id'] ?>">
          Ler post <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- PAGINAÇÃO -->
  <?php if ($totalPaginas > 1): ?>
  <nav class="paginacao">
    <?php if ($pagina > 1): ?>
      <a href="/posts?<?= $qs ?>page=<?= $pagina-1 ?>" class="nav-pag"><i class="bi bi-chevron-left"></i> Anterior</a>
    <?php endif; ?>
    <?php
    $ini = max(1, $pagina - 2);
    $fim = min($totalPaginas, $pagina + 2);
    if ($ini > 1) { echo '<a href="/posts?' . $qs . 'page=1">1</a>'; if ($ini > 2) echo '<span class="reticencias">…</span>'; }
    for ($p = $ini; $p <= $fim; $p++):
    ?>
      <a href="/posts?<?= $qs ?>page=<?= $p ?>" class="<?= $p===$pagina?'pag-ativa':'' ?>"><?= $p ?></a>
    <?php endfor; ?>
    <?php
    if ($fim < $totalPaginas) { if ($fim < $totalPaginas - 1) echo '<span class="reticencias">…</span>'; echo '<a href="/posts?' . $qs . 'page=' . $totalPaginas . '">' . $totalPaginas . '</a>'; }
    ?>
    <?php if ($pagina < $totalPaginas): ?>
      <a href="/posts?<?= $qs ?>page=<?= $pagina+1 ?>" class="nav-pag">Próxima <i class="bi bi-chevron-right"></i></a>
    <?php endif; ?>
  </nav>
  <?php endif; ?>

</div>

<?php if (Auth::check()): ?>
<!-- MODAL CRIAR POST -->
<?php $todasTags = \App\Models\Tag::todas(); ?>
<div class="modal-criar-overlay" id="modalCriarOverlay" onclick="fecharModalFora(event)">
  <div class="modal-criar-box" id="modalCriarBox">
    <h3><i class="bi bi-pencil-square me-2"></i>Novo Post</h3>
    <p class="modal-subtitulo">Compartilhe sua opinião, review ou experiência</p>
    <form method="POST" action="/posts/criar" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Título *</label>
        <input type="text" name="titulo" class="form-control" placeholder="Ex: Por que Elden Ring é uma obra-prima..." required maxlength="200">
      </div>
      <div class="mb-3">
        <label class="form-label">Conteúdo *</label>
        <textarea name="conteudo" class="form-control" placeholder="Escreva seu post aqui..." required minlength="50"></textarea>
        <div class="form-text" style="font-size:.78rem;color:#aaa;">Mínimo 50 caracteres</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Imagem <span style="font-weight:400;color:#aaa;">(opcional)</span></label>
        <input type="file" name="imagem" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
      </div>
      <div class="modal-divider"></div>
      <div class="mb-4">
        <label class="form-label">Tags <span style="font-weight:400;color:#aaa;">(até 5)</span></label>
        <div class="tags-modal-grid">
          <?php foreach ($todasTags as $tag): ?>
            <div class="tag-check-pill">
              <input type="checkbox" name="tags_post[]" id="tag_<?= $tag['id'] ?>" value="<?= $tag['id'] ?>">
              <label for="tag_<?= $tag['id'] ?>"><?= htmlspecialchars($tag['nome']) ?></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn-modal-cancelar" onclick="fecharModal()">Cancelar</button>
        <button type="submit" class="btn-modal-publicar"><i class="bi bi-send me-1"></i> Publicar Post</button>
      </div>
    </form>
  </div>
</div>

<script>
function abrirModalCriar() { document.getElementById('modalCriarOverlay').classList.add('aberto'); }
function fecharModal()      { document.getElementById('modalCriarOverlay').classList.remove('aberto'); }
function fecharModalFora(e) { if (e.target === document.getElementById('modalCriarOverlay')) fecharModal(); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') fecharModal(); });
// Limite de 5 tags
document.querySelectorAll('.tags-modal-grid input[type=checkbox]').forEach(cb => {
  cb.addEventListener('change', function() {
    const sel = document.querySelectorAll('.tags-modal-grid input:checked');
    if (sel.length > 5) this.checked = false;
  });
});
function toggleTagsDropdown() {
  document.getElementById('dropdownTags').classList.toggle('aberto');
  document.getElementById('btnTagsToggle').classList.toggle('ativo');
}
function removerTag(nome) {
  document.querySelectorAll('input[name="tags[]"]').forEach(cb => {
    if (cb.value === nome) cb.checked = false;
  });
  document.getElementById('formFiltro').submit();
}
document.querySelectorAll('.alert').forEach(el => {
  setTimeout(() => { el.style.opacity='0'; setTimeout(()=>el.remove(),500); }, 3500);
});
</script>
<?php endif; ?>
