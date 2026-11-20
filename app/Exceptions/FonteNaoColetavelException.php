<?php

namespace App\Exceptions;

use App\Models\Fonte;
use RuntimeException;

class FonteNaoColetavelException extends RuntimeException
{
    public static function para(Fonte $fonte): self
    {
        $motivo = ! $fonte->fl_ativa ? 'está inativa' : 'não permite acesso automatizado (robots.txt)';

        return new self("A fonte {$fonte->nm_fonte} {$motivo}.");
    }
}
