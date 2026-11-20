<?php

namespace Database\Factories;

use App\Models\Busca;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Busca>
 */
class BuscaFactory extends Factory
{
    protected $model = Busca::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ds_termo' => fake()->words(2, true),
            'nr_resultados' => fake()->numberBetween(0, 50),
            'dt_busca' => now(),
            'id_usuario' => Usuario::factory(),
            'id_sessao_anonima' => null,
        ];
    }
}
