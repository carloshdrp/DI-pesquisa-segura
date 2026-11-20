<?php

namespace App\Rules;

use App\Services\FiltroBuscaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BuscaPermitida implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && ! app(FiltroBuscaService::class)->permitido($value)) {
            $fail(__('Esta busca não é permitida.'));
        }
    }
}
