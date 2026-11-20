<?php

namespace App\Models;

use Database\Factories\BookmarkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bookmark extends Model
{
    /** @use HasFactory<BookmarkFactory> */
    use HasFactory;

    protected $table = 'tb_bookmark';

    protected $primaryKey = 'id_bookmark';

    public $timestamps = false;

    protected $fillable = [
        'dt_criacao',
        'id_usuario',
        'id_pagina_indexada',
        'id_grupo_bookmark',
    ];

    protected function casts(): array
    {
        return [
            'dt_criacao' => 'datetime',
            'id_usuario' => 'integer',
            'id_pagina_indexada' => 'integer',
            'id_grupo_bookmark' => 'integer',
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

    /**
     * @return BelongsTo<GrupoBookmark, $this>
     */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(
            GrupoBookmark::class,
            'id_grupo_bookmark',
            'id_grupo_bookmark'
        );
    }

    /**
     * @return HasMany<LinkCompartilhamento, $this>
     */
    public function links(): HasMany
    {
        return $this->hasMany(
            LinkCompartilhamento::class,
            'id_bookmark',
            'id_bookmark'
        );
    }
}
