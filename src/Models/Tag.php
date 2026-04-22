<?php

namespace App\Models;

use App\Core\Database;

class Tag
{
    public static function todas(): array
    {
        return Database::get()
            ->query("SELECT id, nome FROM tags ORDER BY nome ASC")
            ->fetchAll();
    }
}
