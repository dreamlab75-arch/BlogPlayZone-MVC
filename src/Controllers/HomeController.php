<?php

namespace App\Controllers;

use App\Core\View;
use App\Models\{Post, Noticia};

class HomeController
{
    public function index(): void
    {
        $posts    = Post::emAlta(3);
        $noticias = Noticia::recentes(5);
        $slides   = Noticia::destaquesSemana(3);
        if (empty($slides)) $slides = Noticia::recentes(3);

        View::render('pages/home', compact('posts', 'noticias', 'slides'));
    }
}
