<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessaoAnonima extends Model
{
    protected $table = 'tb_sessao_anonima';

    protected $primaryKey = 'id_sessao_anonima';

    public $timestamps = false;

    protected $fillable = [
        'ds_identificador',
        'nr_buscas_hoje',
        'dt_referencia_contador',
        'dt_criacao',
    ];

    protected function casts(): array
    {
        return [
            'nr_buscas_hoje' => 'integer',
            'dt_referencia_contador' => 'date',
            'dt_criacao' => 'datetime',
        ];
    }
}
