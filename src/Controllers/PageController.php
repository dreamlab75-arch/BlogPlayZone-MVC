<?php

namespace App\Controllers;

use App\Core\View;

class PageController
{
    public function quemSomos(): void
    {
        View::render('pages/quem-somos');
    }

    public function politica(): void
    {
        View::render('pages/privacidade');
    }
}
