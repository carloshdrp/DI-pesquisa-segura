<?php

use App\Enums\OrigemColeta;
use App\Jobs\ColetarTermoJob;
use App\Models\Busca;
use App\Models\FilaColeta;
use App\Models\Fonte;
use App\Models\PaginaIndexada;
use App\Models\SessaoAnonima;
use App\Models\Usuario;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\UsaIndiceFulltext;

uses(UsaIndiceFulltext::class);

function paginaSobre(string $texto, array $fonte = [], array $pagina = []): PaginaIndexada
{
    return PaginaIndexada::factory()
        ->for(Fonte::factory()->create($fonte), 'fonte')
        ->create($pagina + [
            'ds_titulo' => $texto,
            'ds_resumo' => $texto,
            'ds_conteudo_texto' => $texto,
        ]);
}

beforeEach(function () {
    // Evita que a coleta em segundo plano rode de verdade (a fila dos testes é síncrona).
    Queue::fake();

    config(['busca.minimo_resultados' => 1]);
});

test('busca publica devolve as paginas que combinam com o termo e o link da origem', function () {
    paginaSobre('redes neurais convolucionais', pagina: ['ds_url' => 'https://origem.test/redes']);
    paginaSobre('historia da arte moderna');

    $this->getJson(route('busca.index', ['q' => 'redes neurais']))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.url', 'https://origem.test/redes')
        ->assertJsonPath('data.0.titulo', 'redes neurais convolucionais')
        ->assertJsonPath('termo', 'redes neurais');
});

test('a ordem considera a credibilidade da fonte', function () {
    paginaSobre('redes neurais profundas', ['nr_credibilidade' => 1], ['ds_url' => 'https://baixa.test/']);
    paginaSobre('redes neurais profundas', ['nr_credibilidade' => 10], ['ds_url' => 'https://alta.test/']);

    $this->getJson(route('busca.index', ['q' => 'redes neurais']))
        ->assertOk()
        ->assertJsonPath('data.0.url', 'https://alta.test/')
        ->assertJsonPath('data.1.url', 'https://baixa.test/');
});

test('nao devolve paginas de fontes inativas', function () {
    paginaSobre('redes neurais ocultas', ['fl_ativa' => false]);

    $this->getJson(route('busca.index', ['q' => 'redes neurais']))
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('registra a busca do usuario autenticado', function () {
    paginaSobre('redes neurais');
    $usuario = Usuario::factory()->create();

    $this->actingAs($usuario)->getJson(route('busca.index', ['q' => 'redes neurais']))->assertOk();

    $busca = Busca::sole();

    expect($busca->ds_termo)->toBe('redes neurais')
        ->and($busca->nr_resultados)->toBe(1)
        ->and($busca->id_usuario)->toBe($usuario->id_usuario)
        ->and($busca->id_sessao_anonima)->toBeNull();
});

test('registra a busca do visitante na sessao anonima', function () {
    paginaSobre('redes neurais');

    $this->getJson(route('busca.index', ['q' => 'redes neurais']))->assertOk();

    $busca = Busca::sole();

    expect($busca->id_usuario)->toBeNull()
        ->and($busca->id_sessao_anonima)->toBe(SessaoAnonima::sole()->id_sessao_anonima);
});

test('as paginas seguintes nao registram nova busca nem gastam o limite diario', function () {
    config(['busca.por_pagina' => 1, 'busca.limite_anonimo' => 1]);
    paginaSobre('redes neurais um');
    paginaSobre('redes neurais dois');

    $sessao = SessaoAnonima::factory()->create();
    // getJson só envia cookies com withCredentials().
    $this->withCredentials()->withCookie('sessao_anonima', $sessao->ds_identificador);

    $this->getJson(route('busca.index', ['q' => 'redes neurais']))
        ->assertOk()
        ->assertJsonPath('meta.last_page', 2);

    $this->getJson(route('busca.index', ['q' => 'redes neurais', 'page' => 2]))->assertOk();

    expect(Busca::count())->toBe(1)
        ->and($sessao->refresh()->nr_buscas_hoje)->toBe(1);
});

test('o limite diario bloqueia a busca do visitante', function () {
    config(['busca.limite_anonimo' => 1]);
    paginaSobre('redes neurais');

    $sessao = SessaoAnonima::factory()->create();
    // getJson só envia cookies com withCredentials().
    $this->withCredentials()->withCookie('sessao_anonima', $sessao->ds_identificador);

    $this->getJson(route('busca.index', ['q' => 'redes neurais']))->assertOk();
    $this->getJson(route('busca.index', ['q' => 'redes neurais']))->assertStatus(429);
});

test('termo bloqueado e recusado sem registrar busca nem pedir coleta', function () {
    config(['busca.termos_bloqueados' => ['pornografia']]);

    $this->getJson(route('busca.index', ['q' => 'pornografia']))->assertUnprocessable();

    expect(Busca::count())->toBe(0)
        ->and(FilaColeta::count())->toBe(0);

    Queue::assertNothingPushed();
});

test('poucos resultados pedem uma nova coleta em segundo plano', function () {
    config(['busca.minimo_resultados' => 3]);
    paginaSobre('redes neurais');

    $this->getJson(route('busca.index', ['q' => 'Redes Neurais']))->assertOk();

    $fila = FilaColeta::sole();

    expect($fila->tp_origem)->toBe(OrigemColeta::Busca)
        ->and($fila->ds_termo_busca)->toBe('redes neurais')
        ->and($fila->id_fonte)->toBeNull();

    Queue::assertPushedOn('coleta', ColetarTermoJob::class);
});

test('nenhum resultado tambem pede coleta', function () {
    $this->getJson(route('busca.index', ['q' => 'assunto inexistente']))
        ->assertOk()
        ->assertJsonCount(0, 'data');

    expect(FilaColeta::count())->toBe(1);
});

test('resultados suficientes nao pedem coleta', function () {
    config(['busca.minimo_resultados' => 2]);
    paginaSobre('redes neurais um');
    paginaSobre('redes neurais dois');

    $this->getJson(route('busca.index', ['q' => 'redes neurais']))->assertOk();

    expect(FilaColeta::count())->toBe(0);

    Queue::assertNothingPushed();
});

test('o mesmo termo nao pede coleta de novo dentro do intervalo de recoleta', function () {
    $this->getJson(route('busca.index', ['q' => 'assunto raro']))->assertOk();
    $this->getJson(route('busca.index', ['q' => 'ASSUNTO  raro!']))->assertOk();

    expect(FilaColeta::count())->toBe(1);

    Queue::assertPushed(ColetarTermoJob::class, 1);
});

test('depois do intervalo o termo pode pedir coleta de novo', function () {
    config(['busca.recoleta_intervalo_horas' => 6]);

    $this->getJson(route('busca.index', ['q' => 'assunto raro']))->assertOk();

    $this->travel(7)->hours();

    $this->getJson(route('busca.index', ['q' => 'assunto raro']))->assertOk();

    expect(FilaColeta::count())->toBe(2);
});

test('exige o termo de busca', function () {
    $this->getJson(route('busca.index'))->assertJsonValidationErrors('q');
});
