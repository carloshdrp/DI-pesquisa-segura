<?php

namespace Database\Factories;

use App\Models\SessaoAnonima;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SessaoAnonima>
 */
class SessaoAnonimaFactory extends Factory
{
    protected $model = SessaoAnonima::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ds_identificador' => (string) Str::uuid(),
            'nr_buscas_hoje' => 0,
            'dt_referencia_contador' => today(),
            'dt_criacao' => now(),
        ];
    }
}
