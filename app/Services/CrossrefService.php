<?php

namespace App\Services;

use App\Contracts\BuscaContract;
use App\Data\ResultadoBusca;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class CrossrefService implements BuscaContract
{
    const string ENDPOINT = 'https://api.crossref.org/v1/';

    public function urlFonte(): string
    {
        return 'https://www.crossref.org';
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
            ->timeout(15)
            ->get('works', [
                'query' => $query,
                'rows' => 50,
            ])
            ->throw();

        $resultados = [];

        foreach ($response->json('message.items', []) as $item) {
            $url = $item['URL'] ?? null;
            $titulo = $item['title'][0] ?? null;

            if (! is_string($url) || ! is_string($titulo) || trim($titulo) === '') {
                continue;
            }

            $resumo = isset($item['abstract']) && is_string($item['abstract'])
                ? trim(preg_replace('/\s+/', ' ', strip_tags($item['abstract'])) ?? '')
                : null;

            $resultados[] = new ResultadoBusca($url, trim($titulo), $resumo ?: null);
        }

        return $resultados;
    }
}
