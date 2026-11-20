<?php

namespace App\Services;

use App\Contracts\BuscaContract;
use Illuminate\Support\Facades\Http;

class ArXivService implements BuscaContract
{
    const string ENDPOINT = 'http://export.arxiv.org/api/';

    public function __construct() {}

    /**
     * @return array<mixed>
     */
    public function buscar(string $query): array
    {
        $response = Http::get(self::ENDPOINT.'query', [
            'search_query' => "all:{$query}",
            'start' => 0,
            'max_results' => 50,
        ]);

        if ($response->failed()) {
            return [];
        }

        $feed = simplexml_load_string($response->body(), 'SimpleXMLElement', 0, 'http://www.w3.org/2005/Atom');

        $resultados = [];

        foreach ($feed->entry ?? [] as $entry) {
            $resultados[] = [
                'id' => (string) $entry->id,
                'title' => trim((string) $entry->title),
                'summary' => trim((string) $entry->summary),
                'published' => (string) $entry->published,
                'authors' => collect($entry->author ?? [])
                    ->map(fn ($author) => (string) $author->name)
                    ->all(),
            ];
        }

        return $resultados;
    }
}
