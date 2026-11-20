<?php

namespace App\Console\Commands;

use App\Enums\OrigemColeta;
use App\Enums\StatusColeta;
use App\Jobs\ColetarFonteJob;
use App\Models\Fonte;
use Illuminate\Console\Command;

class RecoletarFontesCommand extends Command
{
    protected $signature = 'coleta:fontes';

    protected $description = 'Agenda a recoleta das fontes ativas e liberadas para robôs';

    public function handle(): int
    {
        $agendadas = 0;

        // Fontes inativas ou sem robots liberados nunca entram (RNF02); as que
        // têm coleta pendente ou em execução (nas últimas 24h) esperam a próxima rodada.
        Fonte::coletaveis()
            ->whereDoesntHave('filasColeta', fn ($fila) => $fila
                ->where('tp_origem', OrigemColeta::Fonte)
                ->where('dt_agendamento', '>=', now()->subDay())
                ->whereIn('tp_status', [StatusColeta::Pendente, StatusColeta::Executando]))
            ->each(function (Fonte $fonte) use (&$agendadas) {
                ColetarFonteJob::agendar($fonte);
                $agendadas++;
            });

        $this->info("{$agendadas} fontes agendadas para recoleta.");

        return self::SUCCESS;
    }
}
