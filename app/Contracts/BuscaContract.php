<?php

namespace App\Contracts;

interface BuscaContract
{
    /**
     * @return array<mixed>
     */
    public function buscar(string $query): array;
}
