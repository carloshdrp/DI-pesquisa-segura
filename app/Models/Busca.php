<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Busca extends Model
{
    protected $table = 'tb_busca';

    protected $primaryKey = 'id_busca';

    public $timestamps = false;

    protected $fillable = [
        'ds_termo',
        'nr_resultados',
        'dt_busca',
        'id_usuario',
        'id_sessao_anonima',
    ];

    protected function casts(): array
    {
        return [
            'nr_resultados' => 'integer',
            'dt_busca' => 'datetime',
            'id_usuario' => 'integer',
            'id_sessao_anonima' => 'integer',
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

    public function sessaoAnonima()
    {
        return $this->belongsTo(
            SessaoAnonima::class,
            'id_sessao_anonima',
            'id_sessao_anonima'
        );
    }
}
