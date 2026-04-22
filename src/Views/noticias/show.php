<?php
use App\Core\Auth;
use App\Helpers\Upload;
use App\Models\Noticia;

$titulo = htmlspecialchars($noticia['titulo']);

function formatarDataNoticia(string $data): string {
    $dt = new DateTime($data, new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('America/Sao_Paulo'));
    return $dt->format('d/m/Y') . ' ' . $dt->format('H:i') . 'min';
}
function tempoNoticia(string $data): string {
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

// Botão voltar inteligente
$voltarUrl = '/noticias';
if (!empty($_SERVER['HTTP_REFERER'])) {
    $ref = $_SERVER['HTTP_REFERER'];
    if (strpos($ref, '/noticias/' . $noticia['id']) === false) {
        $voltarUrl = htmlspecialchars($ref);
    }
}
?>

<div class="container noticia-single-wrap">

  <a href="<?= $voltarUrl ?>" class="btn-voltar"><i class="bi bi-arrow-left"></i> Voltar</a>

  <?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success mt-3"><i class="bi bi-check-circle me-2"></i>Notícia publicada com sucesso!</div>
  <?php endif; ?>

  <div class="row g-4">

    <!-- ARTIGO -->
    <div class="col-lg-8">
      <article class="noticia-artigo">

        <div class="noticia-breadcrumb">
          <span class="noticia-breadcrumb-cat" style="color:<?= $corCat ?>;border-bottom:2px solid <?= $corCat ?>;">
            <?= strtoupper(htmlspecialchars($noticia['categoria'])) ?>
          </span>
          <span class="noticia-breadcrumb-sep">/</span>
          <span class="noticia-breadcrumb-tipo">NOTÍCIA</span>
        </div>

        <h1 class="noticia-titulo-grande"><?= htmlspecialchars($noticia['titulo']) ?></h1>

        <?php if (!empty($noticia['resumo'])): ?>
          <p class="noticia-resumo-destaque"><?= htmlspecialchars($noticia['resumo']) ?></p>
        <?php endif; ?>

        <hr class="noticia-divisor">

        <div class="noticia-meta-data">
          <span><i class="bi bi-calendar3 me-1"></i><?= formatarDataNoticia($noticia['data_publicacao']) ?></span>
          <span class="noticia-meta-sep">·</span>
          <span class="tempo-relativo" data-publicacao="<?= $noticia['data_publicacao'] ?>"><?= tempoNoticia($noticia['data_publicacao']) ?></span>
          <span class="noticia-meta-sep">·</span>
          <span><i class="bi bi-eye me-1"></i><?= $noticia['visualizacoes'] ?> views</span>
          <span class="noticia-meta-sep">·</span>
          <span><i class="bi bi-heart me-1"></i><?= $noticia['curtidas'] ?> curtidas</span>
          <span class="noticia-meta-sep">·</span>
          <span><i class="bi bi-chat-dots me-1"></i><?= $noticia['comentarios'] ?> comentários</span>
        </div>

        <hr class="noticia-divisor">

        <div class="noticia-autor-bloco">
          <img src="<?= htmlspecialchars(Upload::url($noticia['autor_avatar'] ?? '', '/img/avatar-default.png')) ?>"
               alt="<?= htmlspecialchars($noticia['autor_nome']) ?>"
               class="noticia-autor-avatar"
               <?= Upload::onerror('/img/avatar-default.png') ?>>
          <div>
            <div class="noticia-autor-nome"><?= htmlspecialchars($noticia['autor_nome']) ?></div>
            <a href="mailto:" class="noticia-autor-email">Enviar E-mail</a>
          </div>
        </div>

        <hr class="noticia-divisor">

        <?php if (!empty($noticia['imagem'])): ?>
          <figure class="noticia-figura">
            <img src="<?= htmlspecialchars(Upload::url($noticia['imagem'])) ?>"
                 alt="<?= htmlspecialchars($noticia['titulo']) ?>"
                 class="noticia-imagem-destaque"
                 onerror="this.onerror=null;this.style.display='none';">
          </figure>
        <?php endif; ?>

        <div class="noticia-conteudo">
          <?php
          $primeiro = true;
          foreach (explode("\n", trim($noticia['conteudo'])) as $p):
            $p = trim($p); if (!$p) continue;
            if ($primeiro): ?><p class="noticia-lead"><?= htmlspecialchars($p) ?></p><?php $primeiro = false;
            else: ?><p><?= htmlspecialchars($p) ?></p><?php
            endif;
          endforeach; ?>
        </div>

        <!-- AÇÕES -->
        <div class="post-acoes">
          <?php if (Auth::check()): ?>
            <form method="POST" action="/noticias/curtir" style="display:inline;">
              <input type="hidden" name="noticia_id" value="<?= $noticia['id'] ?>">
              <button type="submit" class="btn-curtir <?= $usuarioCurtiu ? 'curtido' : '' ?>">
                <i class="bi <?= $usuarioCurtiu ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                <?= $usuarioCurtiu ? 'Curtido' : 'Curtir' ?>
                <strong><?= $noticia['curtidas'] ?></strong>
              </button>
            </form>
          <?php else: ?>
            <span class="btn-curtir" style="cursor:default;opacity:.7;">
              <i class="bi bi-heart"></i> <?= $noticia['curtidas'] ?> curtidas
            </span>
          <?php endif; ?>
          <span class="stat-pill"><i class="bi bi-chat-dots-fill"></i> <?= $noticia['comentarios'] ?> comentário<?= $noticia['comentarios']!=1?'s':'' ?></span>
          <span class="stat-pill"><i class="bi bi-eye-fill"></i> <?= $noticia['visualizacoes'] ?> visualização<?= $noticia['visualizacoes']!=1?'ões':'' ?></span>
          <?php if (!Auth::check()): ?>
            <span class="acoes-login-aviso"><a href="/auth/login">Faça login</a> para curtir e comentar</span>
          <?php endif; ?>
        </div>

        <!-- COMPARTILHAR -->
        <div class="noticia-compartilhar">
          <span class="noticia-compartilhar-label">Compartilhar:</span>
          <a href="https://twitter.com/intent/tweet?text=<?= urlencode($noticia['titulo']) ?>&url=<?= urlencode('http://'.$_SERVER['HTTP_HOST'].'/noticias/'.$noticia['id']) ?>"
             target="_blank" class="noticia-share-btn noticia-share-x" title="X">
            <i class="bi bi-twitter-x"></i>
          </a>
          <a href="https://wa.me/?text=<?= urlencode($noticia['titulo'].' - http://'.$_SERVER['HTTP_HOST'].'/noticias/'.$noticia['id']) ?>"
             target="_blank" class="noticia-share-btn noticia-share-wa" title="WhatsApp">
            <i class="bi bi-whatsapp"></i>
          </a>
          <button onclick="navigator.clipboard.writeText(window.location.href).then(()=>alert('Link copiado!'))"
                  class="noticia-share-btn noticia-share-copy" title="Copiar link">
            <i class="bi bi-link-45deg"></i>
          </button>
        </div>

      </article>

      <!-- COMENTÁRIOS -->
      <section class="comentarios-section">
        <div class="comentarios-titulo">
          <i class="bi bi-chat-square-dots-fill" style="color:#611DF2;"></i>
          Comentários <span><?= count($comentarios) ?></span>
        </div>

        <?php if (Auth::check()): ?>
          <form method="POST" action="/noticias/comentar" class="form-comentario">
            <input type="hidden" name="noticia_id" value="<?= $noticia['id'] ?>">
            <img src="<?= htmlspecialchars(Upload::url(Auth::avatar(), '/img/avatar-default.png')) ?>"
                 alt="Você" class="avatar-mini" <?= Upload::onerror('/img/avatar-default.png') ?>>
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
                <span class="comentario-tempo tempo-relativo" data-publicacao="<?= $c['data'] ?>"><?= tempoNoticia($c['data']) ?></span>
                <p class="comentario-texto"><?= nl2br(htmlspecialchars($c['comentario'])) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>

      <!-- RELACIONADAS -->
      <?php if (!empty($relacionadas)): ?>
      <section class="noticia-relacionadas">
        <h3 class="noticia-relacionadas-titulo">
          <span style="border-bottom:3px solid <?= $corCat ?>;padding-bottom:4px;">Veja também</span>
        </h3>
        <div class="row g-3 mt-1">
          <?php foreach ($relacionadas as $rel): ?>
          <div class="col-md-4">
            <a href="/noticias/<?= $rel['id'] ?>" class="noticia-rel-card">
              <div class="noticia-rel-thumb"
                   style="<?= $rel['imagem'] ? 'background-image:url('.htmlspecialchars(Upload::url($rel['imagem'])).')' : '' ?>">
                <?php if (!$rel['imagem']): ?><i class="bi bi-newspaper"></i><?php endif; ?>
              </div>
              <div class="noticia-rel-corpo">
                <span class="badge <?= Noticia::categoriaBadge($rel['categoria']) ?>" style="font-size:.65rem;">
                  <?= strtoupper($rel['categoria']) ?>
                </span>
                <p class="noticia-rel-titulo"><?= htmlspecialchars($rel['titulo']) ?></p>
                <span class="noticia-rel-data tempo-relativo" data-publicacao="<?= $rel['data_publicacao'] ?>"><?= tempoNoticia($rel['data_publicacao']) ?></span>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

    </div><!-- /col-lg-8 -->

    <!-- SIDEBAR -->
    <div class="col-lg-4">
      <div class="news-sidebar" style="top:90px;">
        <h4><i class="bi bi-fire me-2"></i>Mais Lidas</h4>
        <?php $rank = 1; foreach ($maisLidas as $ml): ?>
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
              <div class="news-item-date mt-1"><i class="bi bi-eye"></i> <?= $ml['visualizacoes'] ?> views</div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>
