<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fonte extends Model
{
    protected $table = 'tb_fonte';

    protected $primaryKey = 'id_fonte';

    public $timestamps = false;

    protected $fillable = [
        'nm_fonte',
        'ds_url_base',
        'nr_credibilidade',
        'fl_robots_permitido',
        'fl_ativa',
        'dt_criacao',
    ];

    protected function casts(): array
    {
        return [
            'nr_credibilidade' => 'integer',
            'fl_robots_permitido' => 'boolean',
            'fl_ativa' => 'boolean',
            'dt_criacao' => 'datetime',
        ];
    }

    public function paginasIndexadas()
    {
        return $this->hasMany(
            PaginaIndexada::class,
            'id_fonte',
            'id_fonte'
        );
    }

    public function filasColeta()
    {
        return $this->hasMany(
            FilaColeta::class,
            'id_fonte',
            'id_fonte'
        );
    }
}
