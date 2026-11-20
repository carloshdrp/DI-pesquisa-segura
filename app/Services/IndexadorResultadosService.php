<?php

namespace App\Services;

use App\Contracts\BuscaContract;
use App\Models\Fonte;
use App\Models\PaginaIndexada;
use Illuminate\Support\Str;

class IndexadorResultadosService
{
    /**
     * Busca o termo na API e grava os resultados em `PaginaIndexada`.
     *
     * Só indexa fontes cadastradas, ativas e liberadas para robôs (RF01, RNF02).
     * Devolve a quantidade de páginas gravadas (novas ou atualizadas).
     */
    public function indexar(BuscaContract $api, string $termo): int
    {
        $fonte = Fonte::coletaveis()->where('ds_url_base', $api->urlFonte())->first();

        if (! $fonte) {
            return 0;
        }

        $gravadas = 0;

        foreach ($api->buscar($termo) as $resultado) {
            $pagina = PaginaIndexada::firstOrNew(['ds_url' => Str::limit($resultado->url, 255, '')]);

            $pagina->fill([
                'ds_titulo' => Str::limit($resultado->titulo, 255, ''),
                'ds_resumo' => $resultado->resumo,
                'dt_atualizado' => now(),
                'id_fonte' => $fonte->id_fonte,
            ]);

            if (! $pagina->exists) {
                $pagina->dt_coletado = now();
            }

            $pagina->save();
            $gravadas++;
        }

        return $gravadas;
    }
}
