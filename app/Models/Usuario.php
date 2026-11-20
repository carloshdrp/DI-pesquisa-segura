<?php

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Usuario extends Authenticatable implements CanResetPasswordContract
{
    /** @use HasFactory<UsuarioFactory> */
    use CanResetPassword, HasFactory, Notifiable;

    protected $table = 'tb_usuario';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    protected $fillable = [
        'nm_usuario',
        'ds_email',
        'ds_senha',
        'dt_criacao',
        'tp_perfil',
        'nr_limite_busca_dia',
        'nr_buscas_hoje',
        'dt_referencia_contador',
    ];

    protected $hidden = [
        'ds_senha',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'ds_senha' => 'hashed',
            'dt_criacao' => 'datetime',
            'tp_perfil' => 'boolean',
            'nr_limite_busca_dia' => 'integer',
            'nr_buscas_hoje' => 'integer',
            'dt_referencia_contador' => 'date',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'ds_senha';
    }

    /**
     * @return HasMany<Bookmark, $this>
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class, 'id_usuario', 'id_usuario');
    }

    /**
     * @return HasMany<GrupoBookmark, $this>
     */
    public function gruposBookmark(): HasMany
    {
        return $this->hasMany(GrupoBookmark::class, 'id_usuario', 'id_usuario');
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->ds_email;
    }

    public function routeNotificationForMail(): string
    {
        return $this->ds_email;
    }

    public function initials(): string
    {
        $initials = Str::initials($this->nm_usuario, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
