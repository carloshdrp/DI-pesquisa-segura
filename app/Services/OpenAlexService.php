<?php

namespace App\Services;

use App\Contracts\BuscaContract;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OpenAlexService implements BuscaContract
{
    const string ENDPOINT = 'https://api.openalex.org/';

    public function __construct() {}

    /**
     * @return array<mixed>
     *
     * @throws ConnectionException
     */
    public function buscar(string $query): array
    {
        $token = config('services.openalex.api_key');

        $response = Http::baseUrl(self::ENDPOINT)
            ->withToken($token)
            ->get('works', [
                'search.semantic' => $query,
            ]);

        if ($response->failed()) {
            return [];
        }

        return $response->json();
    }
}
