<?php
use App\Core\Auth;
use App\Models\Noticia;

$titulo = 'Escrever Notícia';
?>

<div class="container" style="max-width:780px;padding:40px 16px 80px;">

  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
      <h2 class="fw-bold mb-1" style="color:#611DF2;">
        <i class="bi bi-newspaper me-2"></i>Escrever Notícia
      </h2>
      <p class="text-muted mb-0" style="font-size:.9rem;">
        Publicando como <strong><?= htmlspecialchars(Auth::nome()) ?></strong>
        <span class="badge bg-purple text-white ms-1" style="font-size:.7rem;">
          <i class="bi bi-newspaper me-1"></i>Jornalista
        </span>
      </p>
    </div>
    <a href="/noticias" class="btn-voltar" style="margin:0;"><i class="bi bi-arrow-left"></i> Voltar</a>
  </div>

  <?php if (isset($_GET['erro'])): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($_GET['erro']) ?></div>
  <?php endif; ?>

  <div class="adm-card">
    <form action="/noticias/criar" method="POST" enctype="multipart/form-data" id="formNoticia">

      <div class="mb-3">
        <label class="form-label fw-semibold">Título <span style="color:#ef4444;">*</span></label>
        <input type="text" name="titulo" id="inputTitulo" class="form-control adm-form-input"
               placeholder="Título da notícia..." required minlength="5" maxlength="200">
        <div class="form-text" id="contagemTitulo">0 / 200</div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Resumo <span style="color:#ef4444;">*</span>
          <span class="text-muted fw-normal" style="font-size:.82rem;">(aparece nos cards)</span>
        </label>
        <textarea name="resumo" id="inputResumo" class="form-control adm-form-input"
                  placeholder="Um parágrafo curto descrevendo a notícia..."
                  required minlength="10" maxlength="300" rows="2"></textarea>
        <div class="form-text" id="contagemResumo">0 / 300</div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-5">
          <label class="form-label fw-semibold">Categoria <span style="color:#ef4444;">*</span></label>
          <select name="categoria" id="selectCategoria" class="form-select adm-form-input" required>
            <option value="" disabled selected>Selecione...</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat ?>"><?= ucfirst($cat) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-7">
          <label class="form-label fw-semibold">
            Imagem <span class="text-muted fw-normal">(opcional)</span>
          </label>
          <input type="file" name="imagem" id="inputImagem" class="form-control adm-form-input"
                 accept="image/jpeg,image/png,image/webp,image/gif"
                 onchange="previewImagemLocal(this)">
        </div>
      </div>

      <div id="previewImagemWrap" class="mb-3" style="display:none;">
        <div style="background:#0e0e1a;border-radius:12px;overflow:hidden;max-height:260px;">
          <img id="previewImagem" src="" alt="Preview"
               style="width:100%;max-height:260px;object-fit:cover;display:block;">
        </div>
        <div class="form-text"><i class="bi bi-check-circle text-success me-1"></i>Preview da imagem</div>
      </div>

      <div class="mb-3" id="badgePreviewWrap" style="display:none;">
        <span class="badge" id="badgePreview" style="font-size:.8rem;"></span>
        <span class="form-text ms-2">Assim vai aparecer nos cards</span>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Conteúdo <span style="color:#ef4444;">*</span>
          <span class="text-muted fw-normal" style="font-size:.82rem;">(cada parágrafo em uma linha separada)</span>
        </label>
        <textarea name="conteudo" id="inputConteudo" class="form-control adm-form-input"
                  placeholder="Escreva o conteúdo completo da notícia aqui..."
                  required minlength="50" rows="12" style="resize:vertical;"></textarea>
        <div class="form-text" id="contagemConteudo">0 caracteres (mínimo 50)</div>
      </div>

      <div class="d-flex gap-3 justify-content-end flex-wrap">
        <a href="/noticias" class="btn-modal-cancelar" style="text-decoration:none;display:inline-flex;align-items:center;">Cancelar</a>
        <button type="submit" class="btn-modal-publicar" id="btnPublicar">
          <i class="bi bi-send-fill me-1"></i> Publicar Notícia
        </button>
      </div>

    </form>
  </div>
</div>

<script>
function contador(id, labelId, max) {
  const el = document.getElementById(id), lb = document.getElementById(labelId);
  if (!el || !lb) return;
  const upd = () => { const n = el.value.length; lb.textContent = max ? `${n} / ${max}` : `${n} caracteres (mínimo 50)`; lb.style.color = (max && n > max*.9) ? '#ef4444' : ''; };
  el.addEventListener('input', upd); upd();
}
contador('inputTitulo', 'contagemTitulo', 200);
contador('inputResumo', 'contagemResumo', 300);
contador('inputConteudo', 'contagemConteudo', 0);

function previewImagemLocal(input) {
  const wrap = document.getElementById('previewImagemWrap'), img = document.getElementById('previewImagem');
  if (input.files && input.files[0]) {
    const r = new FileReader();
    r.onload = e => { img.src = e.target.result; wrap.style.display = 'block'; };
    r.readAsDataURL(input.files[0]);
  } else { wrap.style.display = 'none'; }
}

const badgeMap = {
  'lançamento':'bg-primary text-white','rumor':'bg-warning text-dark','análise':'bg-info text-white',
  'urgente':'bg-danger text-white','review':'bg-success text-white','prévia':'bg-purple text-white',
  'atualização':'bg-secondary text-white','evento':'bg-dark text-white','hardware':'bg-warning text-dark',
  'negócios':'bg-danger text-white','curiosidade':'bg-info text-white','lista':'bg-primary text-white',
};
document.getElementById('selectCategoria').addEventListener('change', function() {
  const wrap = document.getElementById('badgePreviewWrap'), badge = document.getElementById('badgePreview');
  if (this.value) { badge.className = 'badge ' + (badgeMap[this.value] || 'bg-secondary text-white'); badge.textContent = this.value.toUpperCase(); wrap.style.display = 'block'; }
  else { wrap.style.display = 'none'; }
});

document.querySelectorAll('.alert').forEach(el => { setTimeout(() => { el.style.opacity='0'; setTimeout(()=>el.remove(),500); }, 4000); });
document.getElementById('formNoticia').addEventListener('submit', function() {
  const btn = document.getElementById('btnPublicar');
  btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Publicando...';
});
</script>
