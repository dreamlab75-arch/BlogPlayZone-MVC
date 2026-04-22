<?php
use App\Core\Auth;
use App\Helpers\Upload;
?>
<nav class="navbar navbar-expand-lg navbar-playzone fixed-top">
  <div class="container">

    <a class="navbar-brand d-flex align-items-center" href="/">
      <img src="/img/BlogLogo-01-01.svg" alt="PlayZone">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            style="border-color: rgba(255,255,255,0.5);">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link nav-link-playzone" href="/">Início</a></li>
        <li class="nav-item"><a class="nav-link nav-link-playzone" href="/posts">Posts</a></li>
        <li class="nav-item"><a class="nav-link nav-link-playzone" href="/noticias">Notícias</a></li>
        <li class="nav-item"><a class="nav-link nav-link-playzone" href="/quem-somos">Sobre</a></li>
      </ul>

      <div class="d-flex align-items-center gap-3">
        <div class="search-wrapper">
          <input type="text" class="form-control search-box" placeholder="Buscar...">
          <i class="bi bi-search search-icon"></i>
        </div>

        <div class="account-dropdown">
          <button class="btn btn-account" id="accountToggle" onclick="toggleAccountMenu()">
            <?php if (Auth::check() && Auth::avatar()): ?>
              <img src="<?= htmlspecialchars(Upload::url(Auth::avatar(), '/img/avatar-default.png')) ?>"
                   class="account-avatar" alt="Avatar"
                   <?= Upload::onerror('/img/avatar-default.png') ?>>
            <?php else: ?>
              <i class="bi bi-person-circle"></i>
            <?php endif; ?>
            <i class="bi bi-chevron-down chevron-icon" id="accountChevron"></i>
          </button>

          <div class="account-menu" id="accountMenu">
            <?php if (Auth::check()): ?>
              <?php if (Auth::isAdm()): ?>
                <a href="/admin" class="btn btn-signup w-100 mb-2 d-block text-center">Painel ADM</a>
              <?php endif; ?>
              <a href="/painel" class="btn btn-signup w-100 mb-2 d-block text-center">Minha Conta</a>
              <a href="/auth/logout" class="btn btn-login w-100 d-block text-center">Sair</a>
            <?php else: ?>
              <a href="/auth/login" class="btn btn-login w-100 d-block text-center">Login</a>
              <a href="/auth/cadastro" class="btn btn-signup w-100 mt-2 d-block">Cadastrar-se</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</nav>

<script src="/assets/js/header.js"></script>
