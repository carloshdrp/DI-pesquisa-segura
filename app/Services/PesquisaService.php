<?php

namespace App\Services;

use App\Models\PaginaIndexada;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PesquisaService
{
    private const COLUNAS_FULLTEXT = ['ds_titulo', 'ds_resumo', 'ds_conteudo_texto'];

    /**
     * Busca no índice (FULLTEXT) apenas em fontes ativas. A ordem combina a
     * relevância do texto com a credibilidade da fonte.
     *
     * @return LengthAwarePaginator<int, PaginaIndexada>
     */
    public function pesquisar(string $termo, ?int $porPagina = null): LengthAwarePaginator
    {
        $colunas = implode(', ', self::COLUNAS_FULLTEXT);

        return PaginaIndexada::query()
            ->join('tb_fonte', 'tb_fonte.id_fonte', '=', 'tb_pagina_indexada.id_fonte')
            ->where('tb_fonte.fl_ativa', true)
            ->whereFullText(array_map(fn (string $c) => "tb_pagina_indexada.{$c}", self::COLUNAS_FULLTEXT), $termo)
            ->select('tb_pagina_indexada.*')
            ->with('fonte')
            ->orderByRaw(
                "(MATCH({$colunas}) AGAINST (?)) * (1 + tb_fonte.nr_credibilidade / 10) DESC",
                [$termo],
            )
            ->orderBy('tb_pagina_indexada.id_pagina_indexada')
            ->paginate($porPagina ?? (int) config('busca.por_pagina'));
    }
}
