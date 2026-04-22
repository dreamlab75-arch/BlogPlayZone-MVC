<?php
use App\Core\Auth;
use App\Helpers\Upload;
use App\Models\Tag;

$titulo = htmlspecialchars($post['titulo']);

function tempoPostShow(string $data): string {
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
?>

<div class="container">
  <div class="post-single-wrap">

    <a href="/posts" class="btn-voltar"><i class="bi bi-arrow-left"></i> Voltar aos posts</a>

    <?php if (isset($_GET['sucesso'])): ?>
      <div class="alert alert-success">Ação realizada com sucesso!</div>
    <?php endif; ?>

    <article class="post-single-card">
      <?php if (!empty($tags)): ?>
      <div class="post-single-tags">
        <?php foreach ($tags as $tag): ?>
          <span class="post-single-tag"><?= htmlspecialchars(trim($tag)) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <h1 class="post-single-titulo"><?= htmlspecialchars($post['titulo']) ?></h1>

      <div class="post-single-autor">
        <img src="<?= htmlspecialchars(Upload::url($post['avatar'] ?? '', '/img/avatar-default.png')) ?>"
             alt="<?= htmlspecialchars($post['autor']) ?>"
             class="post-single-avatar"
             <?= Upload::onerror('/img/avatar-default.png') ?>>
        <div class="post-single-autor-info">
          <h6><?= htmlspecialchars($post['autor']) ?></h6>
          <small>
            <span><i class="bi bi-clock"></i>
              <span class="tempo-relativo" data-publicacao="<?= $post['data_publicacao'] ?>">
                <?= tempoPostShow($post['data_publicacao']) ?>
              </span>
            </span>
            <span><i class="bi bi-eye"></i> <?= $post['visualizacoes'] ?> visualizações</span>
            <span><i class="bi bi-heart"></i> <?= $post['curtidas'] ?> curtidas</span>
          </small>
        </div>
      </div>

      <?php if (!empty($post['imagem'])): ?>
        <img src="<?= htmlspecialchars(Upload::url($post['imagem'])) ?>"
             alt="Imagem do post" class="post-single-imagem"
             onerror="this.onerror=null;this.style.display='none';">
      <?php endif; ?>

      <div class="post-single-body">
        <?php foreach (explode("\n", trim($post['conteudo'])) as $p):
          $p = trim($p); if (!$p) continue; ?>
          <p><?= htmlspecialchars($p) ?></p>
        <?php endforeach; ?>
      </div>

      <!-- AÇÕES -->
      <div class="post-acoes">
        <?php if (Auth::check()): ?>
          <form method="POST" action="/posts/curtir" style="display:inline;">
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <button type="submit" class="btn-curtir <?= $usuarioCurtiu ? 'curtido' : '' ?>">
              <i class="bi <?= $usuarioCurtiu ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
              <?= $usuarioCurtiu ? 'Curtido' : 'Curtir' ?>
              <strong><?= $post['curtidas'] ?></strong>
            </button>
          </form>
        <?php else: ?>
          <span class="btn-curtir" style="cursor:default;opacity:.7;">
            <i class="bi bi-heart"></i> <?= $post['curtidas'] ?> curtidas
          </span>
        <?php endif; ?>
        <span class="stat-pill"><i class="bi bi-chat-dots-fill"></i> <?= $post['comentarios'] ?> comentário<?= $post['comentarios'] != 1 ? 's' : '' ?></span>
        <span class="stat-pill"><i class="bi bi-eye-fill"></i> <?= $post['visualizacoes'] ?> visualização<?= $post['visualizacoes'] != 1 ? 'ões' : '' ?></span>
        <?php if (!Auth::check()): ?>
          <span class="acoes-login-aviso"><a href="/auth/login">Faça login</a> para curtir e comentar</span>
        <?php endif; ?>
      </div>
    </article>

    <!-- COMENTÁRIOS -->
    <section class="comentarios-section">
      <div class="comentarios-titulo">
        <i class="bi bi-chat-square-dots-fill" style="color:#611DF2;"></i>
        Comentários <span><?= count($comentarios) ?></span>
      </div>

      <?php if (Auth::check()): ?>
        <form method="POST" action="/posts/comentar" class="form-comentario">
          <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
          <img src="<?= htmlspecialchars(Upload::url(Auth::avatar(), '/img/avatar-default.png')) ?>"
               alt="Você" class="avatar-mini"
               <?= Upload::onerror('/img/avatar-default.png') ?>>
          <textarea name="comentario" placeholder="Escreva um comentário..."
                    required minlength="2"
                    onkeydown="if(event.ctrlKey&&event.key==='Enter')this.form.submit()"></textarea>
          <button type="submit" class="btn-enviar-comentario"><i class="bi bi-send-fill"></i></button>
        </form>
      <?php else: ?>
        <div class="aviso-login-comentar"><a href="/auth/login">Faça login</a> para comentar</div>
      <?php endif; ?>

      <?php if (empty($comentarios)): ?>
        <div class="sem-comentarios"><i class="bi bi-chat-square"></i> Seja o primeiro a comentar!</div>
      <?php else: ?>
        <?php foreach ($comentarios as $c): ?>
          <div class="comentario-item">
            <img src="<?= htmlspecialchars(Upload::url($c['avatar'] ?? '', '/img/avatar-default.png')) ?>"
                 alt="<?= htmlspecialchars($c['nome']) ?>" class="avatar-mini"
                 <?= Upload::onerror('/img/avatar-default.png') ?>>
            <div class="comentario-corpo">
              <span class="comentario-autor"><?= htmlspecialchars($c['nome']) ?></span>
              <span class="comentario-tempo tempo-relativo" data-publicacao="<?= $c['data'] ?>">
                <?= tempoPostShow($c['data']) ?>
              </span>
              <p class="comentario-texto"><?= nl2br(htmlspecialchars($c['comentario'])) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

  </div>
</div>

<script>
document.querySelectorAll('.alert').forEach(el => {
  setTimeout(() => { el.style.opacity='0'; setTimeout(()=>el.remove(),500); }, 3500);
});
</script>
