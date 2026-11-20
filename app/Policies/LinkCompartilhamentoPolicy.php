<?php

namespace App\Policies;

use App\Models\LinkCompartilhamento;
use App\Models\Usuario;

class LinkCompartilhamentoPolicy
{
    public function delete(Usuario $usuario, LinkCompartilhamento $link): bool
    {
        return $link->idUsuarioDono() === $usuario->id_usuario;
    }
}
