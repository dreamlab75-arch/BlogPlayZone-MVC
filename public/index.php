<?php

declare(strict_types=1);

// Autoload via Composer
require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\{Auth, Router};

// Inicia sessão
Auth::start();

// ── Roteador ─────────────────────────────────────────────────────────────────
$router = new Router();

// Home
$router->get('/',               ['HomeController',    'index']);

// Auth
$router->get('/auth/login',     ['AuthController',    'loginForm']);
$router->post('/auth/login',    ['AuthController',    'login']);
$router->get('/auth/cadastro',  ['AuthController',    'cadastroForm']);
$router->post('/auth/cadastro', ['AuthController',    'cadastro']);
$router->get('/auth/logout',    ['AuthController',    'logout']);

// Posts
$router->get('/posts',          ['PostController',    'index']);
$router->get('/posts/{id}',     ['PostController',    'show']);
$router->post('/posts/criar',   ['PostController',    'criar']);
$router->post('/posts/curtir',  ['PostController',    'curtir']);
$router->post('/posts/comentar',['PostController',    'comentar']);
$router->post('/posts/editar',  ['PostController',    'editar']);
$router->get('/posts/deletar',  ['PostController',    'deletar']);

// Notícias
$router->get('/noticias',             ['NoticiaController', 'index']);
$router->get('/noticias/escrever',    ['NoticiaController', 'form']);
$router->get('/noticias/{id}',        ['NoticiaController', 'show']);
$router->post('/noticias/criar',      ['NoticiaController', 'criar']);
$router->post('/noticias/curtir',     ['NoticiaController', 'curtir']);
$router->post('/noticias/comentar',   ['NoticiaController', 'comentar']);
$router->post('/noticias/editar',     ['NoticiaController', 'editar']);
$router->get('/noticias/deletar',     ['NoticiaController', 'deletar']);

// Painel do usuário
$router->get('/painel',                   ['UsuarioController', 'index']);
$router->post('/painel/editar-conta',     ['UsuarioController', 'editarConta']);
$router->post('/painel/trocar-senha',     ['UsuarioController', 'trocarSenha']);

// Admin
$router->get('/admin',                        ['AdminController', 'index']);
$router->post('/admin/adicionar-usuario',     ['AdminController', 'adicionarUsuario']);
$router->post('/admin/editar-usuario',        ['AdminController', 'editarUsuario']);
$router->get('/admin/apagar-usuario',         ['AdminController', 'apagarUsuario']);

// Páginas estáticas
$router->get('/quem-somos',          ['PageController', 'quemSomos']);
$router->get('/politica-privacidade',['PageController', 'politica']);

// ── Dispatch ─────────────────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

// Remove base path se o projeto não estiver na raiz do servidor
// Ex: se estiver em /blogplayzone/, ajuste aqui
$basePath = '';
if ($basePath && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}

$router->dispatch($method, $uri);
