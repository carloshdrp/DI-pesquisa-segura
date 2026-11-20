<?php

namespace App\Models;

use Database\Factories\FonteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fonte extends Model
{
    /** @use HasFactory<FonteFactory> */
    use HasFactory;

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

    /**
     * @return HasMany<PaginaIndexada, $this>
     */
    public function paginasIndexadas(): HasMany
    {
        return $this->hasMany(
            PaginaIndexada::class,
            'id_fonte',
            'id_fonte'
        );
    }

    /**
     * @return HasMany<FilaColeta, $this>
     */
    public function filasColeta(): HasMany
    {
        return $this->hasMany(
            FilaColeta::class,
            'id_fonte',
            'id_fonte'
        );
    }
}
