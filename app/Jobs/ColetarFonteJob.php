<?php

namespace App\Jobs;

use App\Enums\OrigemColeta;
use App\Enums\StatusColeta;
use App\Exceptions\FonteNaoColetavelException;
use App\Models\FilaColeta;
use App\Models\Fonte;
use App\Services\ColetorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ColetarFonteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 600;

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60];
    }

    public function __construct(
        public Fonte $fonte,
        public int $idFilaColeta,
    ) {
        $this->onQueue((string) config('busca.coleta.fila'));
    }

    /**
     * Registra a coleta como pendente na fila e devolve o job já ligado a ela.
     *
     * A linha precisa existir antes do envio: o id viaja no payload, e é por ele
     * que as tentativas seguintes e o failed() encontram o mesmo registro.
     */
    public static function criar(Fonte $fonte): self
    {
        $fila = FilaColeta::create([
            'ds_termo_busca' => null,
            'tp_origem' => OrigemColeta::Fonte,
            'tp_status' => StatusColeta::Pendente,
            'dt_agendamento' => now(),
            'id_fonte' => $fonte->id_fonte,
        ]);

        return new self($fonte, $fila->id_fila_coleta);
    }

    public static function agendar(Fonte $fonte): PendingDispatch
    {
        return dispatch(self::criar($fonte));
    }

    public function handle(ColetorService $coletor): void
    {
        $fila = $this->fila();
        $fila->update(['tp_status' => StatusColeta::Executando]);

        try {
            $coletor->coletar($this->fonte);
        } catch (FonteNaoColetavelException $e) {
            // Não adianta tentar de novo: a fonte está inativa ou não permite robôs.
            $fila->update(['tp_status' => StatusColeta::Falha, 'dt_conclusao' => now()]);
            $this->fail($e);

            return;
        }

        $fila->update(['tp_status' => StatusColeta::Concluida, 'dt_conclusao' => now()]);
    }

    public function failed(Throwable $exception): void
    {
        $this->fila()->update(['tp_status' => StatusColeta::Falha, 'dt_conclusao' => now()]);
    }

    private function fila(): FilaColeta
    {
        return FilaColeta::findOrFail($this->idFilaColeta);
    }
}
