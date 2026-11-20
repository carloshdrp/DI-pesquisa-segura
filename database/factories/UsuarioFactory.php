<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nm_usuario' => fake()->name(),
            'ds_email' => fake()->unique()->safeEmail(),
            'ds_senha' => static::$password ??= Hash::make('password'),
            'dt_criacao' => now(),
            'tp_perfil' => false,
            'nr_limite_busca_dia' => config('busca.limite_autenticado'),
            'nr_buscas_hoje' => 0,
            'dt_referencia_contador' => today(),
            'remember_token' => Str::random(10),
        ];
    }
}
