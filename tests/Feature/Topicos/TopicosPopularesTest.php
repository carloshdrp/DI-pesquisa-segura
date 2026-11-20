<?php

use App\Jobs\ColetarTermoJob;
use App\Models\Busca;
use App\Models\FilaColeta;
use App\Services\TopicosPopularesService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

function buscou(string $termo, int $vezes = 1, ?DateTimeInterface $quando = null): void
{
    Busca::factory()->count($vezes)->create([
        'ds_termo' => $termo,
        'dt_busca' => $quando ?? now(),
    ]);
}

test('agrega os termos mais buscados, juntando variacoes de caixa e acento', function () {
    buscou('Redes Neurais', 2);
    buscou('redes neurais!', 1);
    buscou('história', 2);
    buscou('arte');

    $topicos = app(TopicosPopularesService::class)->calcular(7, 10);

    expect($topicos)->toBe([
        ['termo' => 'redes neurais', 'total' => 3],
        ['termo' => 'historia', 'total' => 2],
        ['termo' => 'arte', 'total' => 1],
    ]);
});

test('respeita o periodo, o limite e ignora termos bloqueados', function () {
    config(['busca.termos_bloqueados' => ['pornografia']]);
    buscou('antigo', 5, now()->subDays(30));
    buscou('pornografia', 4);
    buscou('um', 3);
    buscou('dois', 2);

    $topicos = app(TopicosPopularesService::class)->calcular(7, 1);

    expect($topicos)->toBe([['termo' => 'um', 'total' => 3]]);
});

test('o comando guarda os topicos e pede a coleta de cada um sem repetir', function () {
    buscou('redes neurais', 2);
    buscou('historia');

    $this->artisan('busca:topicos')->assertSuccessful();
    $this->artisan('busca:topicos')->assertSuccessful();

    expect(app(TopicosPopularesService::class)->obter())->toHaveCount(2)
        ->and(FilaColeta::count())->toBe(2);

    Queue::assertPushed(ColetarTermoJob::class, 2);
});

test('o comando aceita periodo e limite', function () {
    buscou('um', 2);
    buscou('dois');

    $this->artisan('busca:topicos --dias=1 --limite=1')->assertSuccessful();

    expect(app(TopicosPopularesService::class)->obter())->toBe([['termo' => 'um', 'total' => 2]]);
});

test('a rota publica devolve os topicos guardados', function () {
    app(TopicosPopularesService::class)->guardar([['termo' => 'redes', 'total' => 4]]);

    $this->getJson(route('topicos.index'))
        ->assertOk()
        ->assertExactJson(['data' => [['termo' => 'redes', 'total' => 4]]]);
});

test('o comando esta agendado a cada hora', function () {
    $evento = collect(app(Schedule::class)->events())
        ->first(fn ($e) => str_contains($e->command, 'busca:topicos'));

    expect($evento)->not->toBeNull()->and($evento->expression)->toBe('0 * * * *');
});
