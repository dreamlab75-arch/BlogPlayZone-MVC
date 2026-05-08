<?php
use App\Helpers\Upload;
use App\Models\Noticia;

$titulo = 'PlayZone — Blog de Videogames';

function tempoPosts(string $data): string {
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

<?php if (!empty($slides)): ?>
<section id="home" class="carousel-section">
  <div class="container">
    <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-indicators">
        <?php foreach ($slides as $i => $s): ?>
          <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="<?= $i ?>"
                  <?= $i===0?'class="active"':'' ?>></button>
        <?php endforeach; ?>
      </div>
      <div class="carousel-inner">
        <?php foreach ($slides as $i => $s): ?>
          <a href="/noticias/<?= $s['id'] ?>"
             class="carousel-item <?= $i===0?'active':'' ?>"
             style="background-image:url('<?= htmlspecialchars(Upload::url($s['imagem'])) ?>');">
            <div class="carousel-caption">
              <span class="badge <?= Noticia::categoriaBadge($s['categoria']) ?> mb-2"><?= strtoupper($s['categoria']) ?></span>
              <h3><?= htmlspecialchars($s['titulo']) ?></h3>
              <p><?= htmlspecialchars(mb_substr($s['resumo'] ?: $s['conteudo'], 0, 120)) ?>...</p>
              <span class="btn-ler-post mt-2" style="display:inline-block;">Ler notícia <i class="bi bi-arrow-right ms-1"></i></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev" onclick="event.preventDefault();event.stopPropagation();">
        <div class="carousel-btn"><i class="bi bi-chevron-left"></i></div>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next" onclick="event.preventDefault();event.stopPropagation();">
        <div class="carousel-btn"><i class="bi bi-chevron-right"></i></div>
      </button>
    </div>
  </div>
</section>
<?php endif; ?>

<div class="container posts-container">
  <div class="row g-4">
    <div class="col-lg-8">
      <?php foreach ($posts as $post):
        $tags = $post['tags'] ? explode(',', $post['tags']) : [];
      ?>
      <div class="post-card">
        <div class="post-author">
          <div class="post-avatar">
            <img src="<?= htmlspecialchars(Upload::url($post['avatar'] ?? '', '/img/avatar-default.png')) ?>"
                 alt="<?= htmlspecialchars($post['autor']) ?>" <?= Upload::onerror('/img/avatar-default.png') ?>>
          </div>
          <div class="post-author-info">
            <h6><?= htmlspecialchars($post['autor']) ?></h6>
            <small><i class="bi bi-clock me-1"></i>
              <span class="tempo-relativo" data-publicacao="<?= $post['data_publicacao'] ?>"><?= tempoPosts($post['data_publicacao']) ?></span>
            </small>
          </div>
        </div>
        <div class="post-tags"><?php foreach ($tags as $t): ?><span class="post-tag"><?= htmlspecialchars(trim($t)) ?></span><?php endforeach; ?></div>
        <h4 class="post-title"><a href="/posts/<?= $post['id'] ?>"><?= htmlspecialchars($post['titulo']) ?></a></h4>
        <p class="post-excerpt"><?= htmlspecialchars(mb_substr($post['conteudo'], 0, 150)) ?>...</p>
        <div class="post-footer">
          <div class="post-stats">
            <span><i class="bi bi-heart-fill" style="color:#e74c3c;"></i> <?= $post['curtidas'] ?></span>
            <span><i class="bi bi-chat-fill"></i> <?= $post['comentarios'] ?></span>
            <span><i class="bi bi-eye-fill"></i> <?= $post['visualizacoes'] ?></span>
          </div>
          <a class="btn-ler-post" href="/posts/<?= $post['id'] ?>">Ler post <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="text-center mt-4">
        <a href="/posts" class="btn-ver-mais">Ver todos os posts <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="news-sidebar" id="noticias">
        <h4><i class="bi bi-newspaper me-2"></i>Últimas Notícias</h4>
        <?php foreach ($noticias as $n): ?>
        <div class="noticia-card">
          <span class="badge <?= Noticia::categoriaBadge($n['categoria']) ?> mb-2"><?= strtoupper($n['categoria']) ?></span>
          <div class="news-item-title"><?= htmlspecialchars($n['titulo']) ?></div>
          <div class="news-item-footer">
            <div class="news-item-date"><i class="bi bi-clock"></i>
              <span class="tempo-relativo" data-publicacao="<?= $n['data_publicacao'] ?>"><?= tempoPosts($n['data_publicacao']) ?></span>
            </div>
            <a class="btn-ler-noticia" href="/noticias/<?= $n['id'] ?>">Ver mais</a>
          </div>
        </div>
        <?php endforeach; ?>
        <div class="text-center mt-4">
          <a href="/noticias" class="btn-ver-mais">Ver todas as notícias <i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
    </div>
  </div>
</div>

<section id="about" style="background: linear-gradient(135deg, #1a0a4a 0%, #611df2 50%, #7957f2 100%);
  padding: 60px 0;
  margin-top: 60px;">
  <div class="container">
    <h2 class="section-title section-title--light">Sobre o PlayZone</h2>
    <div class="row g-4">
      <div class="col-md-4"><div class="sobre-card"><div class="sobre-icon">🎮</div><h5>O que é o PlayZone</h5><p>Um blog criado por gamers para gamers. Aqui você encontra opiniões honestas, análises aprofundadas e as últimas notícias do mundo dos videogames.</p></div></div>
      <div class="col-md-4"><div class="sobre-card"><div class="sobre-icon">📰</div><h5>O que você encontra aqui</h5><p>Posts sobre RPGs, FPS, hardware, e-sports e muito mais. Nossos destaques são selecionados pelo que a comunidade mais lê e discute.</p></div></div>
      <div class="col-md-4"><div class="sobre-card"><div class="sobre-icon">✍️</div><h5>Como participar</h5><p>Qualquer pessoa pode escrever para o PlayZone. Crie sua conta, envie seu post e faça parte desta comunidade!</p></div></div>
    </div>
  </div>
</section>
