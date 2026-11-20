<?php

namespace App\Services;

use App\Contracts\BuscaContract;
use App\Data\ResultadoBusca;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class OpenAlexService implements BuscaContract
{
    const string ENDPOINT = 'https://api.openalex.org/';

    public function urlFonte(): string
    {
        return 'https://openalex.org';
    }

    /**
     * @return list<ResultadoBusca>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function buscar(string $query): array
    {
        $response = Http::baseUrl(self::ENDPOINT)
            ->withToken(config('services.openalex.api_key'))
            ->timeout(15)
            ->get('works', [
                'search' => $query,
                'per-page' => 50,
            ])
            ->throw();

        $resultados = [];

        foreach ($response->json('results', []) as $obra) {
            $url = $obra['doi'] ?? $obra['primary_location']['landing_page_url'] ?? $obra['id'] ?? null;
            $titulo = $obra['title'] ?? $obra['display_name'] ?? null;

            if (! is_string($url) || ! is_string($titulo) || trim($titulo) === '') {
                continue;
            }

            $resultados[] = new ResultadoBusca(
                $url,
                trim($titulo),
                $this->resumo($obra['abstract_inverted_index'] ?? null),
            );
        }

        return $resultados;
    }

    /**
     * A API devolve o resumo como índice invertido (palavra => posições).
     */
    private function resumo(mixed $indice): ?string
    {
        if (! is_array($indice) || $indice === []) {
            return null;
        }

        $palavras = [];

        foreach ($indice as $palavra => $posicoes) {
            foreach ((array) $posicoes as $posicao) {
                $palavras[(int) $posicao] = (string) $palavra;
            }
        }

        ksort($palavras);

        return implode(' ', $palavras);
    }
}
