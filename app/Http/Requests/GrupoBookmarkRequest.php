<?php

namespace App\Http\Requests;

use App\Models\GrupoBookmark;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GrupoBookmarkRequest extends FormRequest
{
    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        /** @var GrupoBookmark|null $grupo */
        $grupo = $this->route('grupo');

        return [
            'nm_grupo' => [
                'required',
                'string',
                'max:150',
                Rule::unique(GrupoBookmark::class, 'nm_grupo')
                    ->where('id_usuario', $this->user()->id_usuario)
                    ->ignore($grupo?->id_grupo_bookmark, 'id_grupo_bookmark'),
            ],
        ];
    }
}
