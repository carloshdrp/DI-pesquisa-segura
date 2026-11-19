<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'tb_usuario';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    protected $fillable = [
        'nm_usuario',
        'ds_email',
        'ds_senha',
        'dt_criacao',
        'tp_perfil',
        'nr_limite_busca_dia',
        'nr_buscas_hoje',
        'dt_referencia_contador',
    ];

    protected function casts(): array
    {
        return [
            'ds_senha' => 'hashed',
            'dt_criacao' => 'datetime',
            'tp_perfil' => 'boolean',
            'nr_limite_busca_dia' => 'integer',
            'nr_buscas_hoje' => 'integer',
            'dt_referencia_contador' => 'date',
        ];
    }
}
