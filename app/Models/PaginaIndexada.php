<?php

namespace App\Models;

use Database\Factories\PaginaIndexadaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaginaIndexada extends Model
{
    /** @use HasFactory<PaginaIndexadaFactory> */
    use HasFactory;

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

    /**
     * @return HasMany<Bookmark, $this>
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(
            Bookmark::class,
            'id_pagina_indexada',
            'id_pagina_indexada'
        );
    }
}
