<?php

use App\Enums\OrigemColeta;
use App\Enums\StatusColeta;
use App\Jobs\ColetarFonteJob;
use App\Models\FilaColeta;
use App\Models\Fonte;
use Database\Seeders\FonteSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

test('agenda so fontes ativas e liberadas para robos, uma fila por fonte', function () {
    $ok = Fonte::factory()->create();
    Fonte::factory()->create(['fl_ativa' => false]);
    Fonte::factory()->create(['fl_robots_permitido' => false]);

    $this->artisan('coleta:fontes')->assertSuccessful();

    Queue::assertPushed(ColetarFonteJob::class, 1);

    $fila = FilaColeta::sole();

    expect($fila->id_fonte)->toBe($ok->id_fonte)
        ->and($fila->tp_origem)->toBe(OrigemColeta::Fonte)
        ->and($fila->tp_status)->toBe(StatusColeta::Pendente);
});

test('nao agenda de novo a fonte que ainda tem coleta em andamento', function () {
    $fonte = Fonte::factory()->create();

    $this->artisan('coleta:fontes')->assertSuccessful();
    $this->artisan('coleta:fontes')->assertSuccessful();

    expect(FilaColeta::count())->toBe(1);

    FilaColeta::query()->update(['tp_status' => StatusColeta::Concluida]);
    $this->artisan('coleta:fontes')->assertSuccessful();

    expect(FilaColeta::where('id_fonte', $fonte->id_fonte)->count())->toBe(2);
});

test('uma coleta pendente ha mais de um dia nao trava a fonte', function () {
    Fonte::factory()->create();
    $this->artisan('coleta:fontes');
    FilaColeta::query()->update(['dt_agendamento' => now()->subDays(2)]);

    $this->artisan('coleta:fontes')->assertSuccessful();

    expect(FilaColeta::count())->toBe(2);
});

test('o seeder cria as fontes e pode rodar de novo sem duplicar', function () {
    $this->seed(FonteSeeder::class);
    $this->seed(FonteSeeder::class);

    expect(Fonte::count())->toBe(3)
        ->and(Fonte::coletaveis()->count())->toBe(3)
        ->and(Fonte::where('nm_fonte', 'OpenAlex')->value('nr_credibilidade'))->toBe(9);
});

test('a recoleta esta agendada todo dia', function () {
    $evento = collect(app(Schedule::class)->events())
        ->first(fn ($e) => str_contains($e->command, 'coleta:fontes'));

    expect($evento)->not->toBeNull()->and($evento->expression)->toBe('0 3 * * *');
});
