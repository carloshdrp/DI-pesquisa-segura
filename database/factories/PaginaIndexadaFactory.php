<?php

namespace Database\Factories;

use App\Models\Fonte;
use App\Models\PaginaIndexada;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaginaIndexada>
 */
class PaginaIndexadaFactory extends Factory
{
    protected $model = PaginaIndexada::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ds_url' => fake()->unique()->url(),
            'ds_titulo' => fake()->sentence(),
            'ds_resumo' => fake()->paragraph(),
            'ds_conteudo_texto' => fake()->paragraphs(3, true),
            'dt_coletado' => now(),
            'dt_atualizado' => now(),
            'id_fonte' => Fonte::factory(),
        ];
    }
}
