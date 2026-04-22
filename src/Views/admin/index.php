<?php
use App\Core\Auth;

$titulo = 'Painel ADM';
?>

<div class="adm-layout">

  <!-- SIDEBAR -->
  <aside class="adm-sidebar">
    <div class="adm-sidebar-logo">
      <img src="/img/BlogLogo-01-01.svg" alt="PlayZone">
    </div>
    <nav class="adm-nav">
      <p class="adm-nav-label">Gerenciar</p>
      <a href="/admin" class="adm-nav-item active">
        <i class="bi bi-people-fill"></i> Usuários
      </a>
    </nav>
    <div class="adm-sidebar-footer">
      <a href="/" class="adm-nav-item"><i class="bi bi-house-fill"></i> Voltar ao blog</a>
      <a href="/auth/logout" class="adm-nav-item adm-nav-item--sair"><i class="bi bi-box-arrow-right"></i> Sair</a>
    </div>
  </aside>

  <!-- CONTEÚDO -->
  <main class="adm-main">
    <div class="adm-topbar">
      <h4 class="adm-page-titulo">Usuários</h4>
      <span class="adm-usuario-logado"><i class="bi bi-person-circle"></i> <?= htmlspecialchars(Auth::nome()) ?></span>
    </div>

    <?php if (isset($_GET['sucesso'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_GET['sucesso']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <div class="adm-add-user-section">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="adm-section-titulo mb-0">Gerenciar Usuários</h5>
        <button class="btn btn-adm-add" onclick="openAddUserModal()">
          <i class="bi bi-person-plus me-2"></i> Novo Usuário
        </button>
      </div>
    </div>

    <div class="adm-card">
      <table class="table adm-table">
        <thead>
          <tr>
            <th class="coluna-id">#</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Perfil</th>
            <th>Ações</th>
          </tr>
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
                <select name="perfil_id" class="form-select form-select-sm" style="width:auto;display:inline-block;"
                        onchange="this.form.submit()">
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
                   class="btn-adm-deletar">
                  <i class="bi bi-trash-fill"></i>
                </a>
              <?php else: ?>
                <span class="text-muted" style="font-size:.8rem;">Você</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- MODAL ADICIONAR USUÁRIO -->
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

<script>
function openAddUserModal()  { document.getElementById('addUserModal').style.display = 'flex'; }
function closeAddUserModal() { document.getElementById('addUserModal').style.display = 'none'; }
document.getElementById('addUserModal').addEventListener('click', e => { if(e.target===e.currentTarget) closeAddUserModal(); });
document.addEventListener('keydown', e => { if(e.key==='Escape') closeAddUserModal(); });
document.querySelectorAll('.alert').forEach(el => { setTimeout(() => { el.style.opacity='0'; setTimeout(()=>el.remove(),500); }, 3000); });
</script>
