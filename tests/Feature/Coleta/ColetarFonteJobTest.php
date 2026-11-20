<?php

use App\Enums\OrigemColeta;
use App\Enums\StatusColeta;
use App\Jobs\ColetarFonteJob;
use App\Models\FilaColeta;
use App\Models\Fonte;
use App\Models\PaginaIndexada;
use App\Services\ColetorService;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config(['busca.coleta.atraso_ms' => 0]);

    $this->fonte = Fonte::factory()->create(['ds_url_base' => 'https://fonte.test/']);

    app()->instance(ColetorService::class, new ColetorService([
        'https://fonte.test/' => '<html><head><title>Inicio</title></head><body>ok</body></html>',
    ]));
});

test('o job coleta a fonte e registra a execucao na fila de coleta', function () {
    dispatch_sync(ColetarFonteJob::criar($this->fonte));

    $fila = FilaColeta::sole();

    expect(PaginaIndexada::count())->toBe(1)
        ->and($fila->tp_status)->toBe(StatusColeta::Concluida)
        ->and($fila->tp_origem)->toBe(OrigemColeta::Fonte)
        ->and($fila->ds_termo_busca)->toBeNull()
        ->and($fila->id_fonte)->toBe($this->fonte->id_fonte)
        ->and($fila->dt_conclusao)->not->toBeNull();
});

test('agendar registra a coleta como pendente antes de enviar para a fila', function () {
    Queue::fake();

    ColetarFonteJob::agendar($this->fonte);

    $fila = FilaColeta::sole();

    expect($fila->tp_status)->toBe(StatusColeta::Pendente)
        ->and($fila->tp_origem)->toBe(OrigemColeta::Fonte)
        ->and($fila->id_fonte)->toBe($this->fonte->id_fonte);

    Queue::assertPushed(ColetarFonteJob::class, fn (ColetarFonteJob $job) => $job->idFilaColeta === $fila->id_fila_coleta);
});

test('o job marca falha na fonte que nao pode ser coletada, sem tentar de novo', function () {
    $fonte = Fonte::factory()->create(['fl_robots_permitido' => false]);

    dispatch_sync(ColetarFonteJob::criar($fonte));

    expect(FilaColeta::sole()->tp_status)->toBe(StatusColeta::Falha)
        ->and(PaginaIndexada::count())->toBe(0);
});

test('o job registra falha quando esgota as tentativas', function () {
    ColetarFonteJob::criar($this->fonte)->failed(new RuntimeException('falha de rede'));

    expect(FilaColeta::sole()->tp_status)->toBe(StatusColeta::Falha);
});

test('o job vai para a fila de coleta, separada da fila padrao', function () {
    Queue::fake();

    ColetarFonteJob::agendar($this->fonte);

    Queue::assertPushedOn('coleta', ColetarFonteJob::class);
});

test('o comando envia a coleta de uma fonte para a fila', function () {
    Queue::fake();

    $this->artisan('coleta:fonte', ['fonte' => $this->fonte->id_fonte])->assertSuccessful();

    Queue::assertPushed(ColetarFonteJob::class);
});

test('o comando falha para fonte inexistente', function () {
    $this->artisan('coleta:fonte', ['fonte' => 9999])->assertFailed();
});
