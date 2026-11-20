<?php

namespace App\Jobs;

use App\Contracts\BuscaContract;
use App\Services\IndexadorResultadosService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Busca um termo numa API externa e indexa os resultados. Roda na fila de
 * coleta, fora do caminho da busca do usuário (RNF04, RNF05).
 */
abstract class BuscaExternaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [30, 120];
    }

    public function __construct(public readonly string $busca)
    {
        $this->onQueue((string) config('busca.coleta.fila'));
    }

    abstract protected function api(): BuscaContract;

    public function handle(IndexadorResultadosService $indexador): void
    {
        $indexador->indexar($this->api(), $this->busca);
    }
}
