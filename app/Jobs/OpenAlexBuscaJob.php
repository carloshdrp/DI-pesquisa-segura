<?php

namespace App\Jobs;

use App\Contracts\BuscaContract;
use App\Services\OpenAlexService;

class OpenAlexBuscaJob extends BuscaExternaJob
{
    protected function api(): BuscaContract
    {
        return app(OpenAlexService::class);
    }
}
