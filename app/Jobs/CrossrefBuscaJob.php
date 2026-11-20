<?php

namespace App\Jobs;

use App\Contracts\BuscaContract;
use App\Services\CrossrefService;

class CrossrefBuscaJob extends BuscaExternaJob
{
    protected function api(): BuscaContract
    {
        return app(CrossrefService::class);
    }
}
