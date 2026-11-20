<?php

namespace App\Enums;

/**
 * Valores aceitos por chk_fila_status em tb_fila_coleta.
 */
enum StatusColeta: string
{
    case Pendente = '1';
    case Executando = '2';
    case Concluida = '3';
    case Falha = '4';
}
