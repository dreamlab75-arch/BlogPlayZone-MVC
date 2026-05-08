<?php $titulo = 'Política de Privacidade'; ?>

<div class="sobre-section">
  <div class="container text-center py-2">
    <h1 class="section-title--light fw-bold mb-2" style="color:#fff;font-size:2.4rem;">
      <i class="bi bi-shield-check me-2"></i>Política de Privacidade
    </h1>
    <p style="color:rgba(255,255,255,.8);font-size:1.05rem;">Última atualização: Janeiro de 2025</p>
  </div>
</div>

<div class="container" style="max-width:860px;padding-top:48px;padding-bottom:80px;">

  <a href="/" class="btn-voltar"><i class="bi bi-arrow-left"></i> Voltar ao início</a>

  <div class="noticia-artigo mb-4">
    <div class="noticia-conteudo">
      <p class="noticia-lead">O PlayZone respeita a sua privacidade. Esta política explica de forma clara quais dados coletamos, como os utilizamos e quais são os seus direitos como usuário.</p>
    </div>
  </div>

  <?php
  $secoes = [
    ['num'=>'1','titulo'=>'Quais dados coletamos','conteudo'=>'<p><strong style="color:#611DF2;">Dados de cadastro:</strong> nome, e-mail e senha (criptografada). Avatar e bio são opcionais.</p><p><strong style="color:#611DF2;">Dados de uso:</strong> posts visualizados, curtidos ou comentados — usados para estatísticas de engajamento.</p><p><strong style="color:#611DF2;">Conteúdo publicado:</strong> posts e comentários são públicos para todos os visitantes.</p>'],
    ['num'=>'2','titulo'=>'Como usamos seus dados','conteudo'=>'<p>Utilizamos seus dados exclusivamente para operar a plataforma, gerar estatísticas e garantir a segurança. Não vendemos nem compartilhamos seus dados pessoais com terceiros para fins comerciais.</p>'],
    ['num'=>'3','titulo'=>'Armazenamento e segurança','conteudo'=>'<p>Seus dados são armazenados em banco de dados local. Senhas são armazenadas como hash SHA-256 — nunca em texto puro. Não utilizamos cookies de rastreamento de terceiros.</p>'],
    ['num'=>'4','titulo'=>'Seus direitos (LGPD)','conteudo'=>'<p>Em conformidade com a <strong>Lei Geral de Proteção de Dados (Lei nº 13.709/2018)</strong>, você tem direito a acesso, correção, exclusão e portabilidade dos seus dados. Contate-nos em <a href="mailto:dream.lab75@gmail.com" style="color:#611DF2;">dream.lab75@gmail.com</a>.</p>'],
    ['num'=>'5','titulo'=>'Alterações nesta política','conteudo'=>'<p>Podemos atualizar esta política periodicamente. O uso continuado da plataforma após alterações constitui aceite das novas condições.</p>'],
  ];
  foreach ($secoes as $s): ?>
  <div class="noticia-artigo mb-4">
    <div class="d-flex align-items-center gap-3 mb-3">
      <span class="badge bg-primary text-white" style="font-size:1rem;padding:8px 14px;border-radius:50px;"><?= $s['num'] ?></span>
      <h2 style="color:#611DF2;font-weight:800;margin:0;"><?= $s['titulo'] ?></h2>
    </div>
    <hr class="noticia-divisor">
    <div class="noticia-conteudo"><?= $s['conteudo'] ?></div>
  </div>
  <?php endforeach; ?>

</div>
