<?php

namespace App\Console\Commands;

use App\Jobs\ColetarFonteJob;
use App\Models\Fonte;
use Illuminate\Console\Command;

class ColetarFonteCommand extends Command
{
    protected $signature = 'coleta:fonte {fonte : Id da fonte} {--sync : Executa agora, sem passar pela fila}';

    protected $description = 'Coleta as páginas de uma fonte cadastrada';

    public function handle(): int
    {
        $fonte = Fonte::find($this->argument('fonte'));

        if (! $fonte) {
            $this->error('Fonte não encontrada.');

            return self::FAILURE;
        }

        if ($this->option('sync')) {
            dispatch_sync(ColetarFonteJob::criar($fonte));
            $this->info("Coleta de {$fonte->nm_fonte} concluída.");
        } else {
            ColetarFonteJob::agendar($fonte);
            $this->info("Coleta de {$fonte->nm_fonte} enviada para a fila.");
        }

        return self::SUCCESS;
    }
}
