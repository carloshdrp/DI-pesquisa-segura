<?php

namespace App\Http\Resources;

use App\Models\PaginaIndexada;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PaginaIndexada
 */
class ResultadoBuscaResource extends JsonResource
{
    /**
     * O resultado aponta para o site de origem (RF14); o conteúdo não é hospedado.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_pagina_indexada,
            'titulo' => $this->ds_titulo,
            'resumo' => $this->ds_resumo,
            'url' => $this->ds_url,
            'fonte' => [
                'nome' => $this->fonte->nm_fonte,
                'credibilidade' => $this->fonte->nr_credibilidade,
            ],
        ];
    }
}
