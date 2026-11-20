<?php

namespace App\Jobs;

use App\Services\ArXivService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ArXivBuscaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $busca
    ) {}

    /**
     * @return array<mixed>
     */
    public function handle(ArXivService $service): array
    {
        return $service->buscar($this->busca);
    }
}
