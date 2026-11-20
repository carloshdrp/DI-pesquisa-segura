<?php

namespace App\Policies;

use App\Models\GrupoBookmark;
use App\Models\Usuario;

class GrupoBookmarkPolicy
{
    public function update(Usuario $usuario, GrupoBookmark $grupo): bool
    {
        return $grupo->id_usuario === $usuario->id_usuario;
    }

    public function delete(Usuario $usuario, GrupoBookmark $grupo): bool
    {
        return $this->update($usuario, $grupo);
    }
}
