<?php


declare(strict_types=1);


require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\{Auth, Router};

Auth::start();

$router = new Router();

$router->get('/',               ['HomeController',    'index']);

$router->get('/auth/login',     ['AuthController',    'loginForm']);
$router->post('/auth/login',    ['AuthController',    'login']);
$router->get('/auth/cadastro',  ['AuthController',    'cadastroForm']);
$router->post('/auth/cadastro', ['AuthController',    'cadastro']);
$router->get('/auth/logout',    ['AuthController',    'logout']);

$router->get('/posts',          ['PostController',    'index']);
$router->get('/posts/{id}',     ['PostController',    'show']);
$router->post('/posts/criar',   ['PostController',    'criar']);
$router->post('/posts/curtir',  ['PostController',    'curtir']);
$router->post('/posts/comentar',['PostController',    'comentar']);
$router->post('/posts/editar',  ['PostController',    'editar']);
$router->get('/posts/deletar',  ['PostController',    'deletar']);

$router->get('/noticias',             ['NoticiaController', 'index']);
$router->get('/noticias/escrever',    ['NoticiaController', 'form']);
$router->get('/noticias/{id}',        ['NoticiaController', 'show']);
$router->post('/noticias/criar',      ['NoticiaController', 'criar']);
$router->post('/noticias/curtir',     ['NoticiaController', 'curtir']);
$router->post('/noticias/comentar',   ['NoticiaController', 'comentar']);
$router->post('/noticias/editar',     ['NoticiaController', 'editar']);
$router->get('/noticias/deletar',     ['NoticiaController', 'deletar']);

$router->get('/painel',                   ['UsuarioController', 'index']);
$router->post('/painel/editar-conta',     ['UsuarioController', 'editarConta']);
$router->post('/painel/trocar-senha',     ['UsuarioController', 'trocarSenha']);

$router->get('/admin',                        ['AdminController', 'index']);
$router->post('/admin/adicionar-usuario',     ['AdminController', 'adicionarUsuario']);
$router->post('/admin/editar-usuario',        ['AdminController', 'editarUsuario']);
$router->get('/admin/apagar-usuario',         ['AdminController', 'apagarUsuario']);
$router->post('/admin/editar-post',           ['AdminController', 'editarPost']);
$router->get('/admin/deletar-post',           ['AdminController', 'deletarPost']);
$router->post('/admin/editar-noticia',        ['AdminController', 'editarNoticia']);
$router->get('/admin/deletar-noticia',        ['AdminController', 'deletarNoticia']);

$router->get('/quem-somos',          ['PageController', 'quemSomos']);
$router->get('/politica-privacidade',['PageController', 'politica']);

$method = $_SERVER['REQUEST_METHOD'];
$uri    = $_SERVER['REQUEST_URI'];

$basePath = '';
if ($basePath && str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}

$router->dispatch($method, $uri);