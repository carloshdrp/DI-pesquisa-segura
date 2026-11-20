<?php

namespace App\Models;

use Database\Factories\LinkCompartilhamentoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkCompartilhamento extends Model
{
    /** @use HasFactory<LinkCompartilhamentoFactory> */
    use HasFactory;

    protected $table = 'tb_link_compartilhamento';

    protected $primaryKey = 'id_link_compartilhamento';

    public $timestamps = false;

    protected $fillable = [
        'ds_token',
        'dt_criacao',
        'dt_revogacao',
        'id_bookmark',
        'id_grupo_bookmark',
    ];

    protected function casts(): array
    {
        return [
            'dt_criacao' => 'datetime',
            'dt_revogacao' => 'datetime',
            'id_bookmark' => 'integer',
            'id_grupo_bookmark' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Bookmark, $this>
     */
    public function bookmark(): BelongsTo
    {
        return $this->belongsTo(
            Bookmark::class,
            'id_bookmark',
            'id_bookmark'
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

    public function estaRevogado(): bool
    {
        return $this->dt_revogacao !== null;
    }

    /**
     * @param  Builder<LinkCompartilhamento>  $query
     */
    public function scopeAtivos(Builder $query): void
    {
        $query->whereNull('dt_revogacao');
    }

    /**
     * Id do usuário dono do favorito ou do grupo compartilhado.
     */
    public function idUsuarioDono(): ?int
    {
        return $this->id_bookmark !== null
            ? $this->bookmark?->id_usuario
            : $this->grupo?->id_usuario;
    }
}
