<?php

namespace App\Contracts;

interface BuscaContract
{
    public function buscar(string $query): array;
}
