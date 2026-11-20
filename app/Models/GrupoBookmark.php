<?php

namespace App\Models;

use Database\Factories\GrupoBookmarkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoBookmark extends Model
{
    /** @use HasFactory<GrupoBookmarkFactory> */
    use HasFactory;

    protected $table = 'tb_grupo_bookmark';

    protected $primaryKey = 'id_grupo_bookmark';

    public $timestamps = false;

    protected $fillable = [
        'nm_grupo',
        'dt_criacao',
        'id_usuario',
    ];

    protected function casts(): array
    {
        return [
            'dt_criacao' => 'datetime',
            'id_usuario' => 'integer',
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
     * @return HasMany<Bookmark, $this>
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(
            Bookmark::class,
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
            'id_grupo_bookmark',
            'id_grupo_bookmark'
        );
    }
}
