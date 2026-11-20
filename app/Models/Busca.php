<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario',
            'id_usuario'
        );
    }

    /**
     * @return BelongsTo<SessaoAnonima, $this>
     */
    public function sessaoAnonima(): BelongsTo
    {
        return $this->belongsTo(
            SessaoAnonima::class,
            'id_sessao_anonima',
            'id_sessao_anonima'
        );
    }
}
