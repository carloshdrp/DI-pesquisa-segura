<?php

use App\Enums\OrigemColeta;
use App\Enums\StatusColeta;
use App\Jobs\ColetarFonteJob;
use App\Jobs\ColetarTermoJob;
use App\Models\FilaColeta;
use App\Models\Fonte;
use Illuminate\Support\Facades\Queue;

function pedidoDeColeta(): FilaColeta
{
    return FilaColeta::create([
        'ds_termo_busca' => 'redes neurais',
        'tp_origem' => OrigemColeta::Busca,
        'tp_status' => StatusColeta::Pendente,
        'dt_agendamento' => now(),
    ]);
}

test('agenda a coleta apenas das fontes ativas e liberadas para robos', function () {
    Queue::fake();

    $elegivel = Fonte::factory()->create();
    Fonte::factory()->create(['fl_ativa' => false]);
    Fonte::factory()->create(['fl_robots_permitido' => false]);

    $fila = pedidoDeColeta();

    (new ColetarTermoJob($fila->id_fila_coleta))->handle();

    Queue::assertPushed(ColetarFonteJob::class, 1);
    Queue::assertPushed(ColetarFonteJob::class, fn (ColetarFonteJob $job) => $job->fonte->is($elegivel));

    expect($fila->refresh()->tp_status)->toBe(StatusColeta::Concluida)
        ->and($fila->dt_conclusao)->not->toBeNull();
});

test('marca o pedido como falho quando o job falha', function () {
    $fila = pedidoDeColeta();

    (new ColetarTermoJob($fila->id_fila_coleta))->failed(new RuntimeException('erro'));

    expect($fila->refresh()->tp_status)->toBe(StatusColeta::Falha);
});

test('o job usa a fila de coleta', function () {
    expect((new ColetarTermoJob(1))->queue)->toBe('coleta');
});
