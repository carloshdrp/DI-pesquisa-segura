<?php

namespace Database\Factories;

use App\Models\Bookmark;
use App\Models\PaginaIndexada;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bookmark>
 */
class BookmarkFactory extends Factory
{
    protected $model = Bookmark::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dt_criacao' => now(),
            'id_usuario' => Usuario::factory(),
            'id_pagina_indexada' => PaginaIndexada::factory(),
            'id_grupo_bookmark' => null,
        ];
    }
}
