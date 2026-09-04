<?php

namespace App\Jobs;

use App\Services\OpenAlexService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class OpenAlexBuscaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $busca
    ) {}

    public function handle(OpenAlexService $service): array
    {
        return $service->buscar($this->busca);
    }
}
