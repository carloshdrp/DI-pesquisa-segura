<?php

namespace App\Policies;

use App\Models\Busca;
use App\Models\Usuario;

class BuscaPolicy
{
    public function delete(Usuario $usuario, Busca $busca): bool
    {
        return $busca->id_usuario === $usuario->id_usuario;
    }
}
