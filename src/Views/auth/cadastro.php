<?php $titulo = 'Cadastro'; ?>
<div class="auth-body-wrap">
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <img src="/img/BlogLogo-01-01.svg" alt="PlayZone">
    </div>
    <h4 class="auth-titulo">Criar conta</h4>
    <p class="auth-subtitulo">Junte-se à comunidade PlayZone</p>

    <?php if (isset($_GET['erro'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <form action="/auth/cadastro" method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Nome</label>
        <input type="text" name="nome" class="form-control auth-input" placeholder="Seu nome" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control auth-input" placeholder="seu@email.com" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Senha</label>
        <input type="password" name="senha" class="form-control auth-input" placeholder="Mínimo 6 caracteres" required minlength="6">
      </div>
      <div class="mb-4">
        <label class="form-label">Avatar <span class="text-muted">(opcional)</span></label>
        <input type="file" name="avatar" class="form-control auth-input"
               accept="image/jpeg,image/png,image/webp,image/gif"
               onchange="previewAvatar(this)">
        <div id="avatarPreviewWrap" style="display:none;margin-top:10px;text-align:center;">
          <img id="avatarPreview" src="" alt="Preview"
               style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid #611DF2;">
        </div>
      </div>
      <button type="submit" class="btn btn-ver-mais w-100">Cadastrar-se</button>
    </form>

    <p class="auth-link">Já tem conta? <a href="/auth/login">Entrar</a></p>
  </div>
</div>
</div>
<script>
function previewAvatar(input) {
  const wrap = document.getElementById('avatarPreviewWrap');
  const img  = document.getElementById('avatarPreview');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => { img.src = e.target.result; wrap.style.display = 'block'; };
    reader.readAsDataURL(input.files[0]);
  } else { wrap.style.display = 'none'; }
}
</script>
