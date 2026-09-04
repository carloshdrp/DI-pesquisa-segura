<?php

namespace App\Jobs;

use App\Services\CrossrefService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class CrossrefBuscaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        private string $busca
    ) {}

    public function handle(CrossrefService $service): void
    {
        $service->buscar($this->busca);
    }
}
