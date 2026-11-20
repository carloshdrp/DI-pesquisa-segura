<?php

namespace App\Models;

use App\Enums\OrigemColeta;
use App\Enums\StatusColeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'tp_origem' => OrigemColeta::class,
            'tp_status' => StatusColeta::class,
            'dt_agendamento' => 'datetime',
            'dt_conclusao' => 'datetime',
            'id_fonte' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Fonte, $this>
     */
    public function fonte(): BelongsTo
    {
        return $this->belongsTo(
            Fonte::class,
            'id_fonte',
            'id_fonte'
        );
    }
}
