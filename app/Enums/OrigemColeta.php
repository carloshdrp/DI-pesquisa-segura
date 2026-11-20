<?php

namespace App\Enums;

/**
 * Valores aceitos por chk_fila_origem em tb_fila_coleta.
 */
enum OrigemColeta: string
{
    /** Coleta de uma fonte cadastrada, feita pela rotina ou manualmente. */
    case Fonte = '1';

    /** Coleta disparada por uma busca com poucos resultados (RF13). */
    case Busca = '2';
}
