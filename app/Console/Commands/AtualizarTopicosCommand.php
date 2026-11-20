<?php

namespace App\Console\Commands;

use App\Services\ColetaPorTermoService;
use App\Services\TopicosPopularesService;
use Illuminate\Console\Command;

class AtualizarTopicosCommand extends Command
{
    protected $signature = 'busca:topicos
        {--dias= : Período considerado, em dias}
        {--limite= : Quantidade de tópicos}';

    protected $description = 'Atualiza os tópicos mais pesquisados e pede a coleta deles';

    public function handle(TopicosPopularesService $topicos, ColetaPorTermoService $coleta): int
    {
        $dias = (int) ($this->option('dias') ?: config('busca.topicos.dias'));
        $limite = (int) ($this->option('limite') ?: config('busca.topicos.limite'));

        $populares = $topicos->calcular($dias, $limite);
        $topicos->guardar($populares);

        // A coleta ignora termos já pedidos dentro do intervalo de recoleta.
        $pedidas = collect($populares)->filter(fn (array $topico) => $coleta->solicitar($topico['termo']))->count();

        $this->info(count($populares)." tópicos atualizados; {$pedidas} coletas pedidas.");

        return self::SUCCESS;
    }
}
