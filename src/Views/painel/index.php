<?php
use App\Core\Auth;
use App\Helpers\Upload;
use App\Models\Noticia;
use App\Models\Tag;

$titulo = 'Meu Painel';

function tempoPainel(string $data): string {
    $agora = new DateTime('now', new DateTimeZone('UTC'));
    $pub   = new DateTime($data, new DateTimeZone('UTC'));
    $diff  = $agora->diff($pub);
    $min   = ($diff->days * 1440) + ($diff->h * 60) + $diff->i;
    if ($diff->days >= 365) { $a = floor($diff->days/365); return "Há {$a} ano" . ($a>1?'s':''); }
    if ($diff->days >= 30)  return 'Há ' . $diff->m . ($diff->m>1?' meses':' mês');
    if ($diff->days >= 7)   { $s = floor($diff->days/7); return "Há {$s} semana" . ($s>1?'s':''); }
    if ($diff->days >= 1)   return 'Há ' . $diff->days . ' dia' . ($diff->days>1?'s':'');
    if ($min >= 60)         return 'Há ' . $diff->h . ' hora' . ($diff->h>1?'s':'');
    if ($min >= 1)          return "Há {$min} minuto" . ($min>1?'s':'');
    return 'Agora mesmo';
}

$cats = ['lançamento','rumor','análise','urgente','review','prévia','atualização','evento','hardware','negócios','curiosidade','lista'];
?>

<div class="painel-layout">

  <aside class="painel-sidebar">
    <div class="painel-sidebar-perfil">
      <div class="painel-sidebar-avatar-wrap">
        <img src="<?= htmlspecialchars(Upload::url($usuario['avatar'] ?? '', '/img/avatar-default.png')) ?>"
             alt="Avatar" class="painel-sidebar-avatar"
             <?= Upload::onerror('/img/avatar-default.png') ?>>
      </div>
      <div class="painel-sidebar-nome"><?= htmlspecialchars($usuario['nome']) ?></div>
      <div class="painel-sidebar-perfil-tipo">
        <?php echo match((int)($usuario['perfil_id'] ?? 2)) {
            1 => '<i class="bi bi-shield-fill-check"></i> Administrador',
            3 => '<i class="bi bi-newspaper"></i> Jornalista',
            default => '<i class="bi bi-person-fill"></i> Leitor',
        }; ?>
      </div>
      <?php if (!empty($usuario['bio'])): ?>
        <p class="painel-sidebar-bio"><?= htmlspecialchars($usuario['bio']) ?></p>
      <?php endif; ?>
    </div>

    <div class="painel-sidebar-stats">
      <div class="painel-stat">
        <span class="painel-stat-num"><?= count($posts) ?></span>
        <span class="painel-stat-label">Posts</span>
      </div>
      <div class="painel-stat">
        <span class="painel-stat-num"><?= array_sum(array_column($posts, 'curtidas')) ?></span>
        <span class="painel-stat-label">Curtidas</span>
      </div>
      <div class="painel-stat">
        <span class="painel-stat-num"><?= array_sum(array_column($posts, 'visualizacoes')) ?></span>
        <span class="painel-stat-label">Views</span>
      </div>
      <?php if ($podeNoticias): ?>
      <div class="painel-stat">
        <span class="painel-stat-num"><?= count($noticias) ?></span>
        <span class="painel-stat-label">Notícias</span>
      </div>
      <?php endif; ?>
    </div>

    <nav class="painel-nav">
      <p class="adm-nav-label">Menu</p>
      <a href="/painel?aba=posts" class="adm-nav-item <?= $aba==='posts'?'active':'' ?>">
        <i class="bi bi-grid-fill"></i> Meus Posts
      </a>
      <?php if ($podeNoticias): ?>
      <a href="/painel?aba=noticias" class="adm-nav-item <?= $aba==='noticias'?'active':'' ?>">
        <i class="bi bi-newspaper"></i> Minhas Notícias
      </a>
      <?php endif; ?>
      <a href="/painel?aba=conta" class="adm-nav-item <?= $aba==='conta'?'active':'' ?>">
        <i class="bi bi-person-gear"></i> Editar Conta
      </a>
    </nav>

    <div class="adm-sidebar-footer">
      <a href="/" class="adm-nav-item"><i class="bi bi-house-fill"></i> Voltar ao blog</a>
      <a href="/auth/logout" class="adm-nav-item adm-nav-item--sair"><i class="bi bi-box-arrow-right"></i> Sair</a>
    </div>
  </aside>

  <main class="painel-main">

    <div class="adm-topbar">
      <h4 class="adm-page-titulo">
        <?= match($aba) { 'conta' => 'Editar Conta', 'noticias' => 'Minhas Notícias', default => 'Meus Posts' } ?>
      </h4>
    </div>

    <?php if (isset($_GET['sucesso'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_GET['sucesso']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <?php if ($aba === 'posts'): ?>
      <?php if (empty($posts)): ?>
        <div class="painel-empty flex flex-column align-items-center">
   <p class="mb-3">Você ainda não escreveu nenhuma post.</p>

    <a href="/posts" class="bi bi-plus-circle me-1, btn-modal-publicar">
        Escrever post
    </a>
</div>
      <?php else: ?>
        <div class="painel-posts-header">
          <p class="resultado-info mb-0"><strong><?= count($posts) ?></strong> post<?= count($posts)!=1?'s':'' ?> publicado<?= count($posts)!=1?'s':'' ?></p>
          <a href="/posts" class="btn-criar-post" style="font-size:.85rem;padding:8px 18px;">
            <i class="bi bi-plus-circle-fill"></i> Novo Post
          </a>
        </div>
        <div class="painel-posts-grid">
          <?php foreach ($posts as $post):
            $tagsPost = $post['tags'] ? explode(',', $post['tags']) : [];
            $trecho   = mb_substr($post['conteudo'], 0, 100);
          ?>
          <div class="painel-post-card">
            <div class="painel-post-thumb"
                 style="<?= $post['imagem'] ? 'background-image:url('.htmlspecialchars(Upload::url($post['imagem'])).')' : '' ?>">
              <?php if (!$post['imagem']): ?><i class="bi bi-controller"></i><?php endif; ?>
            </div>
            <?php if (!empty($tagsPost)): ?>
            <div class="painel-post-tags">
              <?php foreach (array_slice($tagsPost, 0, 2) as $t): ?>
                <span class="post-tag" style="font-size:.7rem;padding:3px 9px;"><?= htmlspecialchars(trim($t)) ?></span>
              <?php endforeach; ?>
              <?php if (count($tagsPost) > 2): ?><span style="font-size:.7rem;color:#aaa;">+<?= count($tagsPost)-2 ?></span><?php endif; ?>
            </div>
            <?php endif; ?>
            <h6 class="painel-post-titulo"><?= htmlspecialchars($post['titulo']) ?></h6>
            <p class="painel-post-trecho"><?= htmlspecialchars($trecho) ?>...</p>
            <div class="painel-post-stats">
              <span><i class="bi bi-heart-fill" style="color:#e74c3c;"></i> <?= $post['curtidas'] ?></span>
              <span><i class="bi bi-chat-fill" style="color:#611DF2;"></i> <?= $post['comentarios'] ?></span>
              <span><i class="bi bi-eye-fill" style="color:#611DF2;"></i> <?= $post['visualizacoes'] ?></span>
            </div>
            <div class="painel-post-data">
              <i class="bi bi-clock"></i>
              <span class="tempo-relativo" data-publicacao="<?= $post['data_publicacao'] ?>"><?= tempoPainel($post['data_publicacao']) ?></span>
            </div>
            <div class="painel-post-acoes">
              <a href="/posts/<?= $post['id'] ?>" class="painel-btn-ver" title="Ver post"><i class="bi bi-eye"></i> Ver</a>
              <button onclick="abrirEditarPost(<?= $post['id'] ?>, <?= htmlspecialchars(json_encode($post['titulo'])) ?>, <?= htmlspecialchars(json_encode($post['conteudo'])) ?>, <?= htmlspecialchars(json_encode($post['imagem'] ?? '')) ?>, <?= htmlspecialchars(json_encode($post['tags'] ?? '')) ?>)"
                      class="painel-btn-editar" title="Editar"><i class="bi bi-pencil-fill"></i> Editar</button>
              <button onclick="confirmarDeletar(<?= $post['id'] ?>, '<?= htmlspecialchars(addslashes($post['titulo'])) ?>')"
                      class="painel-btn-deletar" title="Deletar"><i class="bi bi-trash-fill"></i></button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    <?php elseif ($aba === 'noticias' && $podeNoticias): ?>
      <?php if (empty($noticias)): ?>
        <div class="painel-empty flex flex-column align-items-center">
   <p class="mb-3">Você ainda não publicou nenhuma notícia.</p>

    <a href="/noticias/escrever" class="bi bi-plus-circle me-1, btn-modal-publicar">
        Escrever notícia
    </a>
</div>
      <?php else: ?>
        <div class="painel-posts-header">
          <p class="resultado-info mb-0"><strong><?= count($noticias) ?></strong> notícia<?= count($noticias)!=1?'s':'' ?> publicada<?= count($noticias)!=1?'s':'' ?></p>
          <a href="/noticias/escrever" class="btn-criar-post" style="font-size:.85rem;padding:8px 18px;">
            <i class="bi bi-plus-circle-fill"></i> Nova Notícia
          </a>
        </div>
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
            <p class="painel-post-trecho"><?= htmlspecialchars(mb_substr($noticia['resumo'], 0, 100)) ?>...</p>
            <div class="painel-post-stats">
              <span><i class="bi bi-eye-fill" style="color:#611DF2;"></i> <?= $noticia['visualizacoes'] ?></span>
              <span><i class="bi bi-heart-fill" style="color:#e74c3c;"></i> <?= $noticia['curtidas'] ?></span>
              <span><i class="bi bi-chat-fill" style="color:#611DF2;"></i> <?= $noticia['comentarios'] ?></span>
            </div>
            <div class="painel-post-data">
              <i class="bi bi-clock"></i>
              <span class="tempo-relativo" data-publicacao="<?= $noticia['data_publicacao'] ?>"><?= tempoPainel($noticia['data_publicacao']) ?></span>
            </div>
            <div class="painel-post-acoes">
              <a href="/noticias/<?= $noticia['id'] ?>" class="painel-btn-ver" title="Ver notícia"><i class="bi bi-eye"></i> Ver</a>
              <button onclick="abrirEditarNoticia(<?= $noticia['id'] ?>, <?= htmlspecialchars(json_encode($noticia['titulo'])) ?>, <?= htmlspecialchars(json_encode($noticia['resumo'])) ?>, <?= htmlspecialchars(json_encode($noticia['conteudo'] ?? '')) ?>, <?= htmlspecialchars(json_encode($noticia['imagem'] ?? '')) ?>, <?= htmlspecialchars(json_encode($noticia['categoria'])) ?>)"
                      class="painel-btn-editar" title="Editar"><i class="bi bi-pencil-fill"></i> Editar</button>
              <button onclick="confirmarDeletarNoticia(<?= $noticia['id'] ?>, '<?= htmlspecialchars(addslashes($noticia['titulo'])) ?>')"
                      class="painel-btn-deletar" title="Deletar"><i class="bi bi-trash-fill"></i></button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    <?php elseif ($aba === 'conta'): ?>
      <div class="adm-card" style="max-width:600px;">
        <form action="/painel/editar-conta" method="POST" enctype="multipart/form-data">
          <div class="painel-avatar-preview-wrap mb-4">
            <img src="<?= htmlspecialchars(Upload::url($usuario['avatar'] ?? '', '/img/avatar-default.png')) ?>"
                 alt="Avatar" id="avatarPreview" class="painel-avatar-preview"
                 <?= Upload::onerror('/img/avatar-default.png') ?>>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nome</label>
            <input type="text" name="nome" class="form-control adm-form-input" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control adm-form-input" value="<?= htmlspecialchars($usuario['email']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Avatar <span class="text-muted fw-normal">(opcional)</span></label>
            <input type="file" name="avatar" id="avatarInput" class="form-control adm-form-input"
                   accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewAvatar(this)">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Bio <span class="text-muted fw-normal">(aparece no seu perfil)</span></label>
            <textarea name="bio" class="form-control adm-form-input" rows="3" maxlength="300"
                      placeholder="Conte um pouco sobre você..."><?= htmlspecialchars($usuario['bio'] ?? '') ?></textarea>
            <div class="form-text">Máximo 300 caracteres</div>
          </div>
          <hr class="my-4">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <button type="button" class="btn-modal-cancelar" onclick="abrirModalSenha()"
                    style="display:inline-flex;align-items:center;gap:6px;">
              <i class="bi bi-shield-lock"></i> Trocar senha
            </button>
            <div class="d-flex gap-3">
              <a href="/painel?aba=posts" class="btn-modal-cancelar" style="text-decoration:none;display:inline-flex;align-items:center;">Cancelar</a>
              <button type="submit" class="btn-modal-publicar"><i class="bi bi-check-lg me-1"></i> Salvar Alterações</button>
            </div>
          </div>
        </form>
      </div>
    <?php endif; ?>

  </main>
</div>

<div class="adm-modal-overlay" id="modalDeletar">
  <div class="adm-modal" style="max-width:420px;">
    <h5 class="fw-bold mb-2" style="color:#1a0a4a;">Deletar post?</h5>
    <p class="text-muted mb-4" id="modalDeletarNome" style="font-size:.9rem;"></p>
    <div class="d-flex gap-3 justify-content-end">
      <button class="btn-modal-cancelar" onclick="fecharDeletar()">Cancelar</button>
      <a href="#" id="btnConfirmarDeletar" class="btn-modal-publicar"
         style="background:linear-gradient(135deg,#ef4444,#dc2626);text-decoration:none;">
        <i class="bi bi-trash-fill me-1"></i> Deletar
      </a>
    </div>
  </div>
</div>

<div class="adm-modal-overlay" id="modalEditar">
  <div class="adm-modal" style="max-width:620px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold mb-0" style="color:#611DF2;"><i class="bi bi-pencil-square me-2"></i>Editar Post</h5>
      <button class="btn-close" onclick="fecharEditar()"></button>
    </div>
    <form action="/posts/editar" method="POST" enctype="multipart/form-data" id="formEditarPost">
      <input type="hidden" name="post_id" id="editPostId">
      <div class="mb-3">
        <label class="form-label fw-semibold">Título *</label>
        <input type="text" name="titulo" id="editTitulo" class="form-control adm-form-input" required maxlength="200">
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Conteúdo *</label>
        <textarea name="conteudo" id="editConteudo" class="form-control adm-form-input" rows="6" required minlength="50" style="resize:vertical;"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Imagem <span class="text-muted fw-normal">(opcional — deixe vazio para manter a atual)</span></label>
        <input type="file" name="imagem" class="form-control adm-form-input" accept="image/jpeg,image/png,image/webp,image/gif">
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Tags <span class="text-muted fw-normal">(até 5)</span></label>
        <div class="tags-modal-grid" id="editTagsGrid">
          <?php foreach ($todasTags as $tag): ?>
            <div class="tag-check-pill">
              <input type="checkbox" name="tags_post[]" id="edit_tag_<?= $tag['id'] ?>"
                     value="<?= $tag['id'] ?>" data-nome="<?= htmlspecialchars($tag['nome']) ?>">
              <label for="edit_tag_<?= $tag['id'] ?>"><?= htmlspecialchars($tag['nome']) ?></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="d-flex gap-3 justify-content-end">
        <button type="button" class="btn-modal-cancelar" onclick="fecharEditar()">Cancelar</button>
        <button type="submit" class="btn-modal-publicar"><i class="bi bi-check-lg me-1"></i> Salvar</button>
      </div>
    </form>
  </div>
</div>

<?php if ($podeNoticias): ?>
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
    <form action="/noticias/editar" method="POST" enctype="multipart/form-data" id="formEditarNoticia">
      <input type="hidden" name="noticia_id" id="editNoticiaId">
      <div class="mb-3">
        <label class="form-label fw-semibold">Título *</label>
        <input type="text" name="titulo" id="editNoticiaTitulo" class="form-control adm-form-input" required maxlength="200">
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Resumo *</label>
        <textarea name="resumo" id="editNoticiaResumo" class="form-control adm-form-input" rows="2" required maxlength="300"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Conteúdo *</label>
        <textarea name="conteudo" id="editNoticiaConteudo" class="form-control adm-form-input" rows="6" required minlength="50" style="resize:vertical;"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Imagem <span class="text-muted fw-normal">(opcional — deixe vazio para manter a atual)</span></label>
        <input type="file" name="imagem" class="form-control adm-form-input" accept="image/jpeg,image/png,image/webp,image/gif">
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Categoria *</label>
        <select name="categoria" id="editNoticiaCategoria" class="form-select adm-form-input" required>
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
<?php endif; ?>

<div class="adm-modal-overlay" id="modalSenha">
  <div class="adm-modal" style="max-width:440px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold mb-0" style="color:#611DF2;"><i class="bi bi-shield-lock me-2"></i>Trocar Senha</h5>
      <button class="btn-close" onclick="fecharModalSenha()"></button>
    </div>
    <form action="/painel/trocar-senha" method="POST" id="formSenha">
      <div class="mb-3">
        <label class="form-label fw-semibold">Senha atual <span style="color:#ef4444;">*</span></label>
        <div class="position-relative">
          <input type="password" name="senha_atual" id="senhaAtual" class="form-control adm-form-input" required placeholder="Digite sua senha atual">
          <button type="button" class="btn-toggle-senha" onclick="toggleSenha('senhaAtual', this)" tabindex="-1"><i class="bi bi-eye"></i></button>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label fw-semibold">Nova senha <span style="color:#ef4444;">*</span></label>
        <div class="position-relative">
          <input type="password" name="senha_nova" id="senhaNova" class="form-control adm-form-input" required minlength="6" placeholder="Mínimo 6 caracteres">
          <button type="button" class="btn-toggle-senha" onclick="toggleSenha('senhaNova', this)" tabindex="-1"><i class="bi bi-eye"></i></button>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Confirmar nova senha <span style="color:#ef4444;">*</span></label>
        <div class="position-relative">
          <input type="password" name="senha_confirma" id="senhaConfirma" class="form-control adm-form-input" required placeholder="Repita a nova senha">
          <button type="button" class="btn-toggle-senha" onclick="toggleSenha('senhaConfirma', this)" tabindex="-1"><i class="bi bi-eye"></i></button>
        </div>
      </div>
      <div class="d-flex gap-3 justify-content-end">
        <button type="button" class="btn-modal-cancelar" onclick="fecharModalSenha()">Cancelar</button>
        <button type="submit" class="btn-modal-publicar"><i class="bi bi-check-lg me-1"></i> Salvar nova senha</button>
      </div>
    </form>
  </div>
</div>

<style>
.btn-toggle-senha { position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:#aaa;cursor:pointer;padding:4px;line-height:1; }
.btn-toggle-senha:hover { color:#611DF2; }
</style>

<script>
document.querySelectorAll('.alert').forEach(el => { setTimeout(() => { el.style.opacity='0'; setTimeout(()=>el.remove(),500); }, 3500); });

function confirmarDeletar(id, titulo) {
  document.getElementById('modalDeletarNome').textContent = 'Tem certeza que deseja deletar "' + titulo + '"?';
  document.getElementById('btnConfirmarDeletar').href = '/posts/deletar?id=' + id;
  document.getElementById('modalDeletar').style.display = 'flex';
}
function fecharDeletar() { document.getElementById('modalDeletar').style.display = 'none'; }
document.getElementById('modalDeletar').addEventListener('click', e => { if(e.target===e.currentTarget) fecharDeletar(); });

function abrirEditarPost(id, titulo, conteudo, imagem, tagsStr) {
  document.getElementById('editPostId').value = id;
  document.getElementById('editTitulo').value = titulo;
  document.getElementById('editConteudo').value = conteudo;
  const tags = tagsStr ? tagsStr.split(',').map(t => t.trim()) : [];
  document.querySelectorAll('#editTagsGrid input[type=checkbox]').forEach(cb => { cb.checked = tags.includes(cb.getAttribute('data-nome')); });
  document.getElementById('modalEditar').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function fecharEditar() { document.getElementById('modalEditar').style.display='none'; document.body.style.overflow=''; }
document.getElementById('modalEditar').addEventListener('click', e => { if(e.target===e.currentTarget) fecharEditar(); });
document.querySelectorAll('#editTagsGrid input[type=checkbox]').forEach(cb => {
  cb.addEventListener('change', function() { if(document.querySelectorAll('#editTagsGrid input:checked').length > 5) this.checked = false; });
});

<?php if ($podeNoticias): ?>
function confirmarDeletarNoticia(id, titulo) {
  document.getElementById('modalDeletarNoticiaNome').textContent = 'Tem certeza que deseja deletar "' + titulo + '"?';
  document.getElementById('btnConfirmarDeletarNoticia').href = '/noticias/deletar?id=' + id;
  document.getElementById('modalDeletarNoticia').style.display = 'flex';
}
function fecharDeletarNoticia() { document.getElementById('modalDeletarNoticia').style.display='none'; }
document.getElementById('modalDeletarNoticia')?.addEventListener('click', e => { if(e.target===e.currentTarget) fecharDeletarNoticia(); });

function abrirEditarNoticia(id, titulo, resumo, conteudo, imagem, categoria) {
  document.getElementById('editNoticiaId').value = id;
  document.getElementById('editNoticiaTitulo').value = titulo;
  document.getElementById('editNoticiaResumo').value = resumo;
  document.getElementById('editNoticiaConteudo').value = conteudo;
  const sel = document.getElementById('editNoticiaCategoria');
  if (sel) sel.value = categoria;
  document.getElementById('modalEditarNoticia').style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function fecharEditarNoticia() { document.getElementById('modalEditarNoticia').style.display='none'; document.body.style.overflow=''; }
document.getElementById('modalEditarNoticia')?.addEventListener('click', e => { if(e.target===e.currentTarget) fecharEditarNoticia(); });
<?php endif; ?>

function abrirModalSenha() {
  document.getElementById('formSenha').reset();
  document.getElementById('modalSenha').style.display = 'flex';
  document.body.style.overflow = 'hidden';
  setTimeout(() => document.getElementById('senhaAtual').focus(), 100);
}
function fecharModalSenha() { document.getElementById('modalSenha').style.display='none'; document.body.style.overflow=''; }
document.getElementById('modalSenha').addEventListener('click', e => { if(e.target===e.currentTarget) fecharModalSenha(); });
function toggleSenha(id, btn) {
  const input = document.getElementById(id), icon = btn.querySelector('i');
  input.type = input.type==='password' ? 'text' : 'password';
  icon.className = input.type==='password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const r = new FileReader();
    r.onload = e => document.getElementById('avatarPreview').src = e.target.result;
    r.readAsDataURL(input.files[0]);
  }
}
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { fecharEditar(); fecharDeletar(); fecharModalSenha(); <?php if($podeNoticias): ?>fecharEditarNoticia(); fecharDeletarNoticia();<?php endif; ?> }
});
</script>
