<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaginaIndexada extends Model
{
    protected $table = 'tb_pagina_indexada';

    protected $primaryKey = 'id_pagina_indexada';

    public $timestamps = false;

    protected $fillable = [
        'ds_url',
        'ds_titulo',
        'ds_resumo',
        'ds_conteudo_texto',
        'dt_coletado',
        'dt_atualizado',
        'id_fonte',
    ];

    protected function casts(): array
    {
        return [
            'dt_coletado' => 'datetime',
            'dt_atualizado' => 'datetime',
            'id_fonte' => 'integer',
        ];
    }

    public function fonte()
    {
        return $this->belongsTo(
            Fonte::class,
            'id_fonte',
            'id_fonte'
        );
    }

    public function bookmarks()
    {
        return $this->hasMany(
            Bookmark::class,
            'id_pagina_indexada',
            'id_pagina_indexada'
        );
    }
}
