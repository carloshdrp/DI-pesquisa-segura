<?php

namespace App\Policies;

use App\Models\Bookmark;
use App\Models\Usuario;

class BookmarkPolicy
{
    public function update(Usuario $usuario, Bookmark $bookmark): bool
    {
        return $bookmark->id_usuario === $usuario->id_usuario;
    }
}
