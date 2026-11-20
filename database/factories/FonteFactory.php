<?php

namespace Database\Factories;

use App\Models\Fonte;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fonte>
 */
class FonteFactory extends Factory
{
    protected $model = Fonte::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nm_fonte' => fake()->company(),
            'ds_url_base' => fake()->unique()->url(),
            'nr_credibilidade' => fake()->numberBetween(1, 10),
            'fl_robots_permitido' => true,
            'fl_ativa' => true,
            'dt_criacao' => now(),
        ];
    }
}
