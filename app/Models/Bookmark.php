<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    protected $table = 'tb_bookmark';

    protected $primaryKey = 'id_bookmark';

    public $timestamps = false;

    protected $fillable = [
        'dt_criacao',
        'id_usuario',
        'id_pagina_indexada',
    ];

    protected function casts(): array
    {
        return [
            'dt_criacao' => 'datetime',
            'id_usuario' => 'integer',
            'id_pagina_indexada' => 'integer',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario',
            'id_usuario'
        );
    }

    public function paginaIndexada()
    {
        return $this->belongsTo(
            PaginaIndexada::class,
            'id_pagina_indexada',
            'id_pagina_indexada'
        );
    }
}
