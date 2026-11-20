<?php

namespace App\Jobs;

use App\Contracts\BuscaContract;
use App\Services\ArXivService;

class ArXivBuscaJob extends BuscaExternaJob
{
    protected function api(): BuscaContract
    {
        return app(ArXivService::class);
    }
}
