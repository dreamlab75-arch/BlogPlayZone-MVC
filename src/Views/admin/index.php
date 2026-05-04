<?php
use App\Core\Auth;
use App\Helpers\Upload;
use App\Models\Noticia;

$titulo = 'Painel ADM';
$cats   = $cats ?? Noticia::categoriasValidas();

function tempoAdm(string $data): string {
    $agora = new DateTime('now', new DateTimeZone('UTC'));
    $pub   = new DateTime($data, new DateTimeZone('UTC'));
    $diff  = $agora->diff($pub);
    $min   = ($diff->days * 1440) + ($diff->h * 60) + $diff->i;
    if ($diff->days >= 365) { $a = floor($diff->days/365); return "Há {$a} ano".($a>1?'s':''); }
    if ($diff->days >= 30)  return 'Há '.$diff->m.($diff->m>1?' meses':' mês');
    if ($diff->days >= 7)   { $s = floor($diff->days/7); return "Há {$s} semana".($s>1?'s':''); }
    if ($diff->days >= 1)   return 'Há '.$diff->days.' dia'.($diff->days>1?'s':'');
    if ($min >= 60)         return 'Há '.$diff->h.' hora'.($diff->h>1?'s':'');
    if ($min >= 1)          return "Há {$min} minuto".($min>1?'s':'');
    return 'Agora mesmo';
}
?>

<div class="adm-layout">

  <aside class="adm-sidebar">
    <div class="adm-sidebar-logo">
      <img src="/img/BlogLogo-01-01.svg" alt="PlayZone">
    </div>
    <nav class="adm-nav">
      <p class="adm-nav-label">Gerenciar</p>
      <a href="/admin?aba=usuarios" class="adm-nav-item <?= $aba==='usuarios'?'active':'' ?>">
        <i class="bi bi-people-fill"></i> Usuários
      </a>
      <a href="/admin?aba=posts" class="adm-nav-item <?= $aba==='posts'?'active':'' ?>">
        <i class="bi bi-controller"></i> Posts
      </a>
      <a href="/admin?aba=noticias" class="adm-nav-item <?= $aba==='noticias'?'active':'' ?>">
        <i class="bi bi-newspaper"></i> Notícias
      </a>
    </nav>
    <div class="adm-sidebar-footer">
      <a href="/" class="adm-nav-item"><i class="bi bi-house-fill"></i> Voltar ao blog</a>
      <a href="/auth/logout" class="adm-nav-item adm-nav-item--sair"><i class="bi bi-box-arrow-right"></i> Sair</a>
    </div>
  </aside>

  <main class="adm-main">

    <div class="adm-topbar">
      <h4 class="adm-page-titulo">
        <?= match($aba) { 'posts' => 'Todos os Posts', 'noticias' => 'Todas as Notícias', default => 'Usuários' } ?>
      </h4>
      <span class="adm-usuario-logado"><i class="bi bi-person-circle"></i> <?= htmlspecialchars(Auth::nome()) ?></span>
    </div>

    <?php if (isset($_GET['sucesso'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_GET['sucesso']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <?php if ($aba === 'usuarios'): ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <p class="resultado-info mb-0"><strong><?= count($usuarios) ?></strong> usuário<?= count($usuarios)!=1?'s':'' ?> cadastrado<?= count($usuarios)!=1?'s':'' ?></p>
      <button class="btn btn-adm-add" onclick="openAddUserModal()">
        <i class="bi bi-person-plus me-2"></i> Novo Usuário
      </button>
    </div>
    <div class="adm-card">
      <table class="table adm-table">
        <thead>
          <tr><th class="coluna-id">#</th><th>Nome</th><th>Email</th><th>Perfil</th><th>Ações</th></tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $u): ?>
          <tr>
            <td class="coluna-id"><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['nome']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td>
              <form method="POST" action="/admin/editar-usuario" style="display:inline;">
                <input type="hidden" name="usuario_id" value="<?= $u['id'] ?>">
                <select name="perfil_id" class="form-select form-select-sm adm-perfil-select"
                        onchange="this.form.submit()"
                        <?= $u['id'] === Auth::id() ? 'disabled title="Você não pode alterar seu próprio perfil"' : '' ?>>
                  <?php foreach ($perfis as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id']==$u['perfil_id']?'selected':'' ?>>
                      <?= htmlspecialchars($p['tipo']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
            <td>
              <?php if ($u['id'] !== Auth::id()): ?>
                <a href="/admin/apagar-usuario?id=<?= $u['id'] ?>"
                   onclick="return confirm('Deletar <?= htmlspecialchars(addslashes($u['nome'])) ?>?')"
                   class="btn-adm-deletar"><i class="bi bi-trash-fill"></i></a>
              <?php else: ?>
                <span class="text-muted" style="font-size:.8rem;">Você</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php elseif ($aba === 'posts'): ?>
    <p class="resultado-info mb-4"><strong><?= count($posts) ?></strong> post<?= count($posts)!=1?'s':'' ?> no sistema</p>
    <?php if (empty($posts)): ?>
      <div class="painel-empty"><i class="bi bi-controller"></i><p>Nenhum post publicado ainda.</p></div>
    <?php else: ?>
    <div class="painel-posts-grid">
      <?php foreach ($posts as $post):
        $tagsPost = $post['tags'] ? explode(',', $post['tags']) : [];
      ?>
      <div class="painel-post-card">
        <div class="painel-post-thumb"
             style="<?= $post['imagem'] ? 'background-image:url('.htmlspecialchars(Upload::url($post['imagem'])).')' : '' ?>">
          <?php if (!$post['imagem']): ?><i class="bi bi-controller"></i><?php endif; ?>
        </div>
        <div class="painel-post-tags">
          <?php foreach (array_slice($tagsPost, 0, 2) as $t): ?>
            <span class="post-tag" style="font-size:.7rem;padding:3px 9px;"><?= htmlspecialchars(trim($t)) ?></span>
          <?php endforeach; ?>
        </div>
        <h6 class="painel-post-titulo"><?= htmlspecialchars($post['titulo']) ?></h6>
        <div class="painel-post-autor">
  <i class="bi bi-person-fill"></i>
  <?= htmlspecialchars($post['autor']) ?>
</div>
        <div class="painel-post-stats">
          <span><i class="bi bi-heart-fill" style="color:#e74c3c;"></i> <?= $post['curtidas'] ?></span>
          <span><i class="bi bi-chat-fill" style="color:#611DF2;"></i> <?= $post['comentarios'] ?></span>
          <span><i class="bi bi-eye-fill" style="color:#611DF2;"></i> <?= $post['visualizacoes'] ?></span>
        </div>
        <div class="painel-post-data">
          <i class="bi bi-clock"></i>
          <span class="tempo-relativo" data-publicacao="<?= $post['data_publicacao'] ?>"><?= tempoAdm($post['data_publicacao']) ?></span>
        </div>
        <div class="painel-post-acoes">
          <a href="/posts/<?= $post['id'] ?>" class="painel-btn-ver" title="Ver"><i class="bi bi-eye"></i> Ver</a>
          <button onclick="abrirEditarPost(<?= $post['id'] ?>, <?= htmlspecialchars(json_encode($post['titulo'])) ?>, <?= htmlspecialchars(json_encode($post['conteudo'])) ?>, <?= htmlspecialchars(json_encode($post['imagem'] ?? '')) ?>, <?= htmlspecialchars(json_encode($post['tags'] ?? '')) ?>)"
                  class="painel-btn-editar"><i class="bi bi-pencil-fill"></i> Editar</button>
          <button onclick="confirmarDeletarPost(<?= $post['id'] ?>, '<?= htmlspecialchars(addslashes($post['titulo'])) ?>')"
                  class="painel-btn-deletar"><i class="bi bi-trash-fill"></i></button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php elseif ($aba === 'noticias'): ?>
    <p class="resultado-info mb-4"><strong><?= count($noticias) ?></strong> notícia<?= count($noticias)!=1?'s':'' ?> no sistema</p>
    <?php if (empty($noticias)): ?>
      <div class="painel-empty"><i class="bi bi-newspaper"></i><p>Nenhuma notícia publicada ainda.</p></div>
    <?php else: ?>
    <div class="painel-posts-grid">
      <?php foreach ($noticias as $noticia): ?>
      <div class="painel-post-card">
        <div class="painel-post-thumb"
             style="<?= $noticia['imagem'] ? 'background-image:url('.htmlspecialchars(Upload::url($noticia['imagem'])).')' : '' ?>">
          <?php if (!$noticia['imagem']): ?><i class="bi bi-newspaper"></i><?php endif; ?>
        </div>
        <div class="painel-post-tags">
          <span class="badge <?= Noticia::categoriaBadge($noticia['categoria']) ?>" style="font-size:.7rem;">
            <?= strtoupper($noticia['categoria']) ?>
          </span>
        </div>
        <h6 class="painel-post-titulo"><?= htmlspecialchars($noticia['titulo']) ?></h6>
        <div class="painel-post-autor">
  <i class="bi bi-person-fill"></i>
  <?= htmlspecialchars($noticia['autor_nome']) ?>
</div>
        <div class="painel-post-stats">
          <span><i class="bi bi-heart-fill" style="color:#e74c3c;"></i> <?= $noticia['curtidas'] ?></span>
          <span><i class="bi bi-chat-fill" style="color:#611DF2;"></i> <?= $noticia['comentarios'] ?></span>
          <span><i class="bi bi-eye-fill" style="color:#611DF2;"></i> <?= $noticia['visualizacoes'] ?></span>
        </div>
        <div class="painel-post-data">
          <i class="bi bi-clock"></i>
          <span class="tempo-relativo" data-publicacao="<?= $noticia['data_publicacao'] ?>"><?= tempoAdm($noticia['data_publicacao']) ?></span>
        </div>
        <div class="painel-post-acoes">
          <a href="/noticias/<?= $noticia['id'] ?>" class="painel-btn-ver"><i class="bi bi-eye"></i> Ver</a>
          <button onclick="abrirEditarNoticia(<?= $noticia['id'] ?>, <?= htmlspecialchars(json_encode($noticia['titulo'])) ?>, <?= htmlspecialchars(json_encode($noticia['resumo'] ?? '')) ?>, <?= htmlspecialchars(json_encode($noticia['conteudo'] ?? '')) ?>, <?= htmlspecialchars(json_encode($noticia['imagem'] ?? '')) ?>, <?= htmlspecialchars(json_encode($noticia['categoria'])) ?>)"
                  class="painel-btn-editar"><i class="bi bi-pencil-fill"></i> Editar</button>
          <button onclick="confirmarDeletarNoticia(<?= $noticia['id'] ?>, '<?= htmlspecialchars(addslashes($noticia['titulo'])) ?>')"
                  class="painel-btn-deletar"><i class="bi bi-trash-fill"></i></button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>

  </main>
</div>

<div class="adm-modal-overlay" id="addUserModal">
  <div class="adm-modal">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h5 class="mb-0 fw-bold text-dark">Novo Usuário</h5>
      <button class="btn-close" onclick="closeAddUserModal()"></button>
    </div>
    <form action="/admin/adicionar-usuario" method="POST">
      <div class="adm-form-group">
        <label class="form-label fw-semibold text-dark">Nome</label>
        <input type="text" class="form-control adm-form-input" name="nome" required>
      </div>
      <div class="adm-form-group">
        <label class="form-label fw-semibold text-dark">Email</label>
        <input type="email" class="form-control adm-form-input" name="email" required>
      </div>
      <div class="adm-form-group">
        <label class="form-label fw-semibold text-dark">Senha</label>
        <input type="password" class="form-control adm-form-input" name="senha" required minlength="6">
      </div>
      <div class="adm-form-group">
        <label class="form-label fw-semibold text-dark">Perfil</label>
        <select class="form-select adm-form-input" name="perfil_id" required>
          <option value="">Selecione o perfil</option>
          <?php foreach ($perfis as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['tipo']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="d-flex gap-3 justify-content-end mt-4">
        <button type="button" class="btn btn-secondary" onclick="closeAddUserModal()">Cancelar</button>
        <button type="submit" class="btn btn-adm-add">Criar Usuário</button>
      </div>
    </form>
  </div>
</div>

<div class="adm-modal-overlay" id="modalDeletarPost">
  <div class="adm-modal" style="max-width:420px;">
    <h5 class="fw-bold mb-2" style="color:#1a0a4a;">Deletar post?</h5>
    <p class="text-muted mb-4" id="modalDeletarPostNome" style="font-size:.9rem;"></p>
    <div class="d-flex gap-3 justify-content-end">
      <button class="btn-modal-cancelar" onclick="fecharDeletarPost()">Cancelar</button>
      <a href="#" id="btnConfirmarDeletarPost" class="btn-modal-publicar"
         style="background:linear-gradient(135deg,#ef4444,#dc2626);text-decoration:none;">
        <i class="bi bi-trash-fill me-1"></i> Deletar
      </a>
    </div>
  </div>
</div>

<div class="adm-modal-overlay" id="modalEditarPost">
  <div class="adm-modal" style="max-width:620px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold mb-0" style="color:#611DF2;"><i class="bi bi-pencil-square me-2"></i>Editar Post</h5>
      <button class="btn-close" onclick="fecharEditarPost()"></button>
    </div>
    <form action="/admin/editar-post" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="post_id" id="admEditPostId">
      <div class="mb-3">
        <label class="form-label fw-semibold">Título *</label>
        <input type="text" name="titulo" id="admEditPostTitulo" class="form-control adm-form-input" required maxlength="200">
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Conteúdo *</label>
        <textarea name="conteudo" id="admEditPostConteudo" class="form-control adm-form-input" rows="6" required minlength="50" style="resize:vertical;"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Imagem <span class="text-muted fw-normal">(deixe vazio para manter a atual)</span></label>
        <input type="file" name="imagem" class="form-control adm-form-input" accept="image/jpeg,image/png,image/webp,image/gif">
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Tags <span class="text-muted fw-normal">(até 5)</span></label>
        <div class="tags-modal-grid" id="admEditTagsGrid">
          <?php foreach ($todasTags as $tag): ?>
            <div class="tag-check-pill">
              <input type="checkbox" name="tags_post[]" id="adm_tag_<?= $tag['id'] ?>"
                     value="<?= $tag['id'] ?>" data-nome="<?= htmlspecialchars($tag['nome']) ?>">
              <label for="adm_tag_<?= $tag['id'] ?>"><?= htmlspecialchars($tag['nome']) ?></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="d-flex gap-3 justify-content-end">
        <button type="button" class="btn-modal-cancelar" onclick="fecharEditarPost()">Cancelar</button>
        <button type="submit" class="btn-modal-publicar"><i class="bi bi-check-lg me-1"></i> Salvar</button>
      </div>
    </form>
  </div>
</div>

<div class="adm-modal-overlay" id="modalDeletarNoticia">
  <div class="adm-modal" style="max-width:420px;">
    <h5 class="fw-bold mb-2" style="color:#1a0a4a;">Deletar notícia?</h5>
    <p class="text-muted mb-4" id="modalDeletarNoticiaNome" style="font-size:.9rem;"></p>
    <div class="d-flex gap-3 justify-content-end">
      <button class="btn-modal-cancelar" onclick="fecharDeletarNoticia()">Cancelar</button>
      <a href="#" id="btnConfirmarDeletarNoticia" class="btn-modal-publicar"
         style="background:linear-gradient(135deg,#ef4444,#dc2626);text-decoration:none;">
        <i class="bi bi-trash-fill me-1"></i> Deletar
      </a>
    </div>
  </div>
</div>

<div class="adm-modal-overlay" id="modalEditarNoticia">
  <div class="adm-modal" style="max-width:660px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold mb-0" style="color:#611DF2;"><i class="bi bi-pencil-square me-2"></i>Editar Notícia</h5>
      <button class="btn-close" onclick="fecharEditarNoticia()"></button>
    </div>
    <form action="/admin/editar-noticia" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="noticia_id" id="admEditNoticiaId">
      <div class="mb-3">
        <label class="form-label fw-semibold">Título *</label>
        <input type="text" name="titulo" id="admEditNoticiaTitulo" class="form-control adm-form-input" required maxlength="200">
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Resumo *</label>
        <textarea name="resumo" id="admEditNoticiaResumo" class="form-control adm-form-input" rows="2" required maxlength="300"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Conteúdo *</label>
        <textarea name="conteudo" id="admEditNoticiaConteudo" class="form-control adm-form-input" rows="6" required minlength="50" style="resize:vertical;"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Imagem <span class="text-muted fw-normal">(deixe vazio para manter a atual)</span></label>
        <input type="file" name="imagem" class="form-control adm-form-input" accept="image/jpeg,image/png,image/webp,image/gif">
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Categoria *</label>
        <select name="categoria" id="admEditNoticiaCategoria" class="form-select adm-form-input" required>
          <?php foreach ($cats as $c): ?>
            <option value="<?= $c ?>"><?= ucfirst($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="d-flex gap-3 justify-content-end">
        <button type="button" class="btn-modal-cancelar" onclick="fecharEditarNoticia()">Cancelar</button>
        <button type="submit" class="btn-modal-publicar"><i class="bi bi-check-lg me-1"></i> Salvar</button>
      </div>
    </form>
  </div>
</div>

<style>
.adm-perfil-select { font-size:.82rem;padding:4px 28px 4px 10px;border-radius:20px;border:1.5px solid #ede8ff;background-color:#f8f5ff;color:#611DF2;font-weight:600;cursor:pointer;transition:border-color .2s,box-shadow .2s;min-width:130px; }
.adm-perfil-select:hover:not(:disabled) { border-color:#611DF2;box-shadow:0 0 0 3px rgba(97,29,242,.1); }
.adm-perfil-select:disabled { opacity:.5;cursor:not-allowed; }
</style>

<script>
document.querySelectorAll('.alert').forEach(el => { setTimeout(() => { el.style.opacity='0'; setTimeout(()=>el.remove(),500); }, 3000); });

function openAddUserModal()  { document.getElementById('addUserModal').style.display='flex'; }
function closeAddUserModal() { document.getElementById('addUserModal').style.display='none'; }
document.getElementById('addUserModal').addEventListener('click', e => { if(e.target===e.currentTarget) closeAddUserModal(); });

function confirmarDeletarPost(id, titulo) {
  document.getElementById('modalDeletarPostNome').textContent = 'Tem certeza que deseja deletar "' + titulo + '"?';
  document.getElementById('btnConfirmarDeletarPost').href = '/admin/deletar-post?id=' + id;
  document.getElementById('modalDeletarPost').style.display = 'flex';
}
function fecharDeletarPost() { document.getElementById('modalDeletarPost').style.display='none'; }
document.getElementById('modalDeletarPost').addEventListener('click', e => { if(e.target===e.currentTarget) fecharDeletarPost(); });

function abrirEditarPost(id, titulo, conteudo, imagem, tagsStr) {
  document.getElementById('admEditPostId').value = id;
  document.getElementById('admEditPostTitulo').value = titulo;
  document.getElementById('admEditPostConteudo').value = conteudo;
  const tags = tagsStr ? tagsStr.split(',').map(t => t.trim()) : [];
  document.querySelectorAll('#admEditTagsGrid input[type=checkbox]').forEach(cb => { cb.checked = tags.includes(cb.getAttribute('data-nome')); });
  document.getElementById('modalEditarPost').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function fecharEditarPost() { document.getElementById('modalEditarPost').style.display='none'; document.body.style.overflow=''; }
document.getElementById('modalEditarPost').addEventListener('click', e => { if(e.target===e.currentTarget) fecharEditarPost(); });
document.querySelectorAll('#admEditTagsGrid input[type=checkbox]').forEach(cb => {
  cb.addEventListener('change', function() { if(document.querySelectorAll('#admEditTagsGrid input:checked').length > 5) this.checked = false; });
});

function confirmarDeletarNoticia(id, titulo) {
  document.getElementById('modalDeletarNoticiaNome').textContent = 'Tem certeza que deseja deletar "' + titulo + '"?';
  document.getElementById('btnConfirmarDeletarNoticia').href = '/admin/deletar-noticia?id=' + id;
  document.getElementById('modalDeletarNoticia').style.display = 'flex';
}
function fecharDeletarNoticia() { document.getElementById('modalDeletarNoticia').style.display='none'; }
document.getElementById('modalDeletarNoticia').addEventListener('click', e => { if(e.target===e.currentTarget) fecharDeletarNoticia(); });

function abrirEditarNoticia(id, titulo, resumo, conteudo, imagem, categoria) {
  document.getElementById('admEditNoticiaId').value = id;
  document.getElementById('admEditNoticiaTitulo').value = titulo;
  document.getElementById('admEditNoticiaResumo').value = resumo;
  document.getElementById('admEditNoticiaConteudo').value = conteudo;
  const sel = document.getElementById('admEditNoticiaCategoria');
  if (sel) sel.value = categoria;
  document.getElementById('modalEditarNoticia').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function fecharEditarNoticia() { document.getElementById('modalEditarNoticia').style.display='none'; document.body.style.overflow=''; }
document.getElementById('modalEditarNoticia').addEventListener('click', e => { if(e.target===e.currentTarget) fecharEditarNoticia(); });

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeAddUserModal(); fecharDeletarPost(); fecharEditarPost();
    fecharDeletarNoticia(); fecharEditarNoticia();
  }
});
</script>