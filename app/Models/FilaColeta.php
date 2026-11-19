<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilaColeta extends Model
{
    protected $table = 'tb_fila_coleta';

    protected $primaryKey = 'id_fila_coleta';

    public $timestamps = false;

    protected $fillable = [
        'ds_termo_busca',
        'tp_origem',
        'tp_status',
        'dt_agendamento',
        'dt_conclusao',
        'id_fonte',
    ];

    protected function casts(): array
    {
        return [
            'dt_agendamento' => 'datetime',
            'dt_conclusao' => 'datetime',
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
}
