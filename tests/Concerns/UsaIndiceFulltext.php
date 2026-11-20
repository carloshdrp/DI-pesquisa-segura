<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * O índice FULLTEXT do InnoDB só enxerga linhas já commitadas, e a suíte roda
 * cada teste dentro de uma transação. Com este trait o teste grava de verdade
 * no banco e limpa as tabelas ao terminar.
 */
trait UsaIndiceFulltext
{
    /** @var list<string> */
    private array $tabelasParaLimpar = [
        'tb_link_compartilhamento',
        'tb_bookmark',
        'tb_grupo_bookmark',
        'tb_busca',
        'tb_fila_coleta',
        'tb_pagina_indexada',
        'tb_fonte',
        'tb_sessao_anonima',
        'tb_usuario',
    ];

    protected function setUpUsaIndiceFulltext(): void
    {
        while (DB::transactionLevel() > 0) {
            DB::commit();
        }
    }

    protected function tearDownUsaIndiceFulltext(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ($this->tabelasParaLimpar as $tabela) {
            DB::table($tabela)->truncate();
        }

        Schema::enableForeignKeyConstraints();
    }
}
