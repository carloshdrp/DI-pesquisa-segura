<?php

namespace Database\Factories;

use App\Models\GrupoBookmark;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GrupoBookmark>
 */
class GrupoBookmarkFactory extends Factory
{
    protected $model = GrupoBookmark::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nm_grupo' => fake()->unique()->words(2, true),
            'dt_criacao' => now(),
            'id_usuario' => Usuario::factory(),
        ];
    }
}
