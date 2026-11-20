<?php

namespace App\Services;

use App\Enums\OrigemColeta;
use App\Enums\StatusColeta;
use App\Jobs\ColetarTermoJob;
use App\Models\FilaColeta;

class ColetaPorTermoService
{
    public function __construct(private readonly FiltroBuscaService $filtro) {}

    /**
     * Pede uma nova coleta em segundo plano para o termo (RF13).
     *
     * Não repete o pedido se o mesmo termo já foi solicitado dentro do
     * intervalo de recoleta. Devolve se um novo pedido foi criado.
     */
    public function solicitar(string $termo): bool
    {
        $chave = $this->filtro->normalizar($termo);
        $limite = now()->subHours((int) config('busca.recoleta_intervalo_horas'));

        $jaSolicitado = FilaColeta::query()
            ->where('ds_termo_busca', $chave)
            ->where('tp_origem', OrigemColeta::Busca)
            ->where('dt_agendamento', '>=', $limite)
            ->exists();

        if ($jaSolicitado) {
            return false;
        }

        $fila = FilaColeta::create([
            'ds_termo_busca' => $chave,
            'tp_origem' => OrigemColeta::Busca,
            'tp_status' => StatusColeta::Pendente,
            'dt_agendamento' => now(),
            'id_fonte' => null,
        ]);

        ColetarTermoJob::dispatch($fila->id_fila_coleta);

        return true;
    }
}
