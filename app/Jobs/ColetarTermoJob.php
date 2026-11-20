<?php

namespace App\Jobs;

use App\Enums\StatusColeta;
use App\Models\FilaColeta;
use App\Models\Fonte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

/**
 * Atende o pedido de coleta gerado por uma busca com poucos resultados:
 * agenda a coleta de cada fonte que pode ser acessada por robôs.
 */
class ColetarTermoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1;

    public function __construct(public int $idFilaColeta)
    {
        $this->onQueue((string) config('busca.coleta.fila'));
    }

    public function handle(): void
    {
        $fila = FilaColeta::findOrFail($this->idFilaColeta);
        $fila->update(['tp_status' => StatusColeta::Executando]);

        Fonte::coletaveis()
            ->each(fn (Fonte $fonte) => ColetarFonteJob::agendar($fonte));

        // As APIs também são consultadas pelo termo; o indexador ignora fontes não liberadas.
        foreach ([OpenAlexBuscaJob::class, ArXivBuscaJob::class, CrossrefBuscaJob::class] as $job) {
            $job::dispatch((string) $fila->ds_termo_busca);
        }

        $fila->update(['tp_status' => StatusColeta::Concluida, 'dt_conclusao' => now()]);
    }

    public function failed(Throwable $exception): void
    {
        FilaColeta::whereKey($this->idFilaColeta)
            ->update(['tp_status' => StatusColeta::Falha, 'dt_conclusao' => now()]);
    }
}
