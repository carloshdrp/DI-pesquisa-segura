<?php

namespace App\Http\Requests;

use App\Models\GrupoBookmark;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoverBookmarkRequest extends FormRequest
{
    /**
     * Aceita apenas grupos do próprio usuário; null remove o favorito do grupo.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'id_grupo_bookmark' => [
                'nullable',
                'integer',
                Rule::exists(GrupoBookmark::class, 'id_grupo_bookmark')
                    ->where('id_usuario', $this->user()->id_usuario),
            ],
        ];
    }
}
