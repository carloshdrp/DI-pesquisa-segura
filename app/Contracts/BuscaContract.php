<?php

namespace App\Contracts;

use App\Data\ResultadoBusca;

interface BuscaContract
{
    /**
     * URL base da `Fonte` cadastrada a que os resultados pertencem.
     */
    public function urlFonte(): string;

    /**
     * Busca o termo na API e devolve os resultados já normalizados. Resultados
     * sem URL ou sem título são descartados.
     *
     * @return list<ResultadoBusca>
     */
    public function buscar(string $query): array;
}
