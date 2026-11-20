<?php

namespace App\Data;

/**
 * Formato comum dos resultados das APIs externas (OpenAlex, arXiv, Crossref).
 */
final readonly class ResultadoBusca
{
    public function __construct(
        public string $url,
        public string $titulo,
        public ?string $resumo = null,
    ) {}
}
