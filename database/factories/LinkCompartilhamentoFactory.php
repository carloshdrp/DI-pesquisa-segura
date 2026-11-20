<?php

namespace Database\Factories;

use App\Models\Bookmark;
use App\Models\LinkCompartilhamento;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LinkCompartilhamento>
 */
class LinkCompartilhamentoFactory extends Factory
{
    protected $model = LinkCompartilhamento::class;

    /**
     * Por padrão o link compartilha um favorito.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ds_token' => Str::random(64),
            'dt_criacao' => now(),
            'dt_revogacao' => null,
            'id_bookmark' => Bookmark::factory(),
            'id_grupo_bookmark' => null,
        ];
    }

    public function revogado(): static
    {
        return $this->state(fn () => ['dt_revogacao' => now()]);
    }
}
