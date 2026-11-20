<?php

namespace App\Services;

use Illuminate\Support\Str;

class FiltroBuscaService
{
    /**
     * Indica se o termo pode ser buscado (não contém nenhum termo bloqueado).
     */
    public function permitido(string $termo): bool
    {
        $texto = ' '.$this->normalizar($termo).' ';

        foreach ($this->bloqueados() as $bloqueado) {
            if (str_contains($texto, ' '.$bloqueado.' ')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Minúsculas, sem acentos e sem pontuação, com espaços colapsados.
     */
    public function normalizar(string $texto): string
    {
        $texto = Str::of($texto)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', ' ');

        return trim((string) $texto);
    }

    /**
     * @return list<string>
     */
    private function bloqueados(): array
    {
        $bloqueados = [];

        foreach ((array) config('busca.termos_bloqueados', []) as $termo) {
            $normalizado = $this->normalizar((string) $termo);

            if ($normalizado !== '') {
                $bloqueados[] = $normalizado;
            }
        }

        return $bloqueados;
    }
}
