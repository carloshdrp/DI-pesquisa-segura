<?php

namespace App\Http\Requests;

use App\Models\Bookmark;
use App\Models\GrupoBookmark;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CriarLinkCompartilhamentoRequest extends FormRequest
{
    /**
     * Informe um favorito OU um grupo, sempre do próprio usuário.
     *
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'id_bookmark' => [
                'required_without:id_grupo_bookmark',
                'prohibits:id_grupo_bookmark',
                'nullable',
                'integer',
                Rule::exists(Bookmark::class, 'id_bookmark')
                    ->where('id_usuario', $this->user()->id_usuario),
            ],
            'id_grupo_bookmark' => [
                'required_without:id_bookmark',
                'prohibits:id_bookmark',
                'nullable',
                'integer',
                Rule::exists(GrupoBookmark::class, 'id_grupo_bookmark')
                    ->where('id_usuario', $this->user()->id_usuario),
            ],
        ];
    }
}
