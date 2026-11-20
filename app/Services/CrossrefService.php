<?php

namespace App\Services;

use App\Contracts\BuscaContract;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class CrossrefService implements BuscaContract
{
    public function __construct() {}

    const string ENDPOINT = 'https://api.crossref.org/v1/';

    /**
     * @return array<mixed>
     *
     * @throws ConnectionException
     */
    public function buscar(string $query): array
    {
        $response = Http::get(self::ENDPOINT.'works?query='.$query);

        if ($response->failed()) {
            return [];
        }

        return $response->json();
    }
}
