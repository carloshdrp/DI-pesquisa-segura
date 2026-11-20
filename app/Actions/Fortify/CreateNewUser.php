<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Usuario;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): Usuario
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return Usuario::create([
            'nm_usuario' => $input['name'],
            'ds_email' => $input['email'],
            'ds_senha' => $input['password'],
            'dt_criacao' => now(),
            'tp_perfil' => false,
            'nr_limite_busca_dia' => config('busca.limite_autenticado'),
            'nr_buscas_hoje' => 0,
            'dt_referencia_contador' => today(),
        ]);
    }
}
