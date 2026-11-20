<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;

class UsuarioUserProvider extends EloquentUserProvider
{
    /**
     * Mapeia as credenciais do Laravel/Fortify para as colunas de tb_usuario.
     *
     * @param  array<string, mixed>  $credentials
     */
    public function retrieveByCredentials(#[\SensitiveParameter] array $credentials)
    {
        if (array_key_exists('email', $credentials)) {
            $credentials['ds_email'] = $credentials['email'];
            unset($credentials['email']);
        }

        return parent::retrieveByCredentials($credentials);
    }
}
