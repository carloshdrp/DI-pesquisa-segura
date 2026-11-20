<?php

namespace App\Services;

use App\Contracts\BuscaContract;
use App\Data\ResultadoBusca;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class ArXivService implements BuscaContract
{
    const string ENDPOINT = 'http://export.arxiv.org/api/';

    public function urlFonte(): string
    {
        return 'https://arxiv.org';
    }

    /**
     * @return list<ResultadoBusca>
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function buscar(string $query): array
    {
        $response = Http::timeout(15)
            ->get(self::ENDPOINT.'query', [
                'search_query' => "all:{$query}",
                'start' => 0,
                'max_results' => 50,
            ])
            ->throw();

        $feed = simplexml_load_string($response->body(), 'SimpleXMLElement', 0, 'http://www.w3.org/2005/Atom');

        if ($feed === false) {
            return [];
        }

        $resultados = [];

        foreach ($feed->entry as $entry) {
            $url = trim((string) $entry->id);
            $titulo = trim((string) preg_replace('/\s+/', ' ', (string) $entry->title));
            $resumo = trim((string) preg_replace('/\s+/', ' ', (string) $entry->summary));

            if ($url === '' || $titulo === '') {
                continue;
            }

            $resultados[] = new ResultadoBusca($url, $titulo, $resumo ?: null);
        }

        return $resultados;
    }
}
