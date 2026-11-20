<?php

use App\Models\SessaoAnonima;
use App\Models\Usuario;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'limite.busca'])->get('/_teste-busca', fn () => 'ok');

    config(['busca.limite_anonimo' => 2]);
});

test('visitante recebe uma sessao anonima e um cookie na primeira busca', function () {
    $this->get('/_teste-busca')
        ->assertOk()
        ->assertCookie('sessao_anonima');

    $sessao = SessaoAnonima::sole();

    expect($sessao->nr_buscas_hoje)->toBe(1);
});

test('visitante e bloqueado ao atingir o limite diario', function () {
    $sessao = SessaoAnonima::factory()->create();

    $this->withCookie('sessao_anonima', $sessao->ds_identificador);

    $this->get('/_teste-busca')->assertOk();
    $this->get('/_teste-busca')->assertOk();

    $this->get('/_teste-busca')
        ->assertStatus(429)
        ->assertHeader('Retry-After');

    expect($sessao->refresh()->nr_buscas_hoje)->toBe(2)
        ->and(SessaoAnonima::count())->toBe(1);
});

test('cookie invalido gera uma nova sessao anonima', function () {
    $this->withCookie('sessao_anonima', 'nao-e-um-uuid')
        ->get('/_teste-busca')
        ->assertOk();

    expect(SessaoAnonima::count())->toBe(1);
});

test('o contador do visitante zera no dia seguinte', function () {
    $sessao = SessaoAnonima::factory()->create([
        'nr_buscas_hoje' => 2,
        'dt_referencia_contador' => today()->subDay(),
    ]);

    $this->withCookie('sessao_anonima', $sessao->ds_identificador)
        ->get('/_teste-busca')
        ->assertOk();

    $sessao->refresh();

    expect($sessao->nr_buscas_hoje)->toBe(1)
        ->and($sessao->dt_referencia_contador->isToday())->toBeTrue();
});

test('usuario autenticado usa o proprio limite diario', function () {
    $usuario = Usuario::factory()->create(['nr_limite_busca_dia' => 3]);

    $this->actingAs($usuario);

    $this->get('/_teste-busca')->assertOk();
    $this->get('/_teste-busca')->assertOk();
    $this->get('/_teste-busca')->assertOk();
    $this->get('/_teste-busca')->assertStatus(429);

    expect($usuario->refresh()->nr_buscas_hoje)->toBe(3)
        ->and(SessaoAnonima::count())->toBe(0);
});

test('usuario autenticado pode buscar mais do que um visitante', function () {
    $usuario = Usuario::factory()->create();

    $this->actingAs($usuario);

    foreach (range(1, config('busca.limite_anonimo') + 1) as $_) {
        $this->get('/_teste-busca')->assertOk();
    }
});

test('o contador do usuario zera no dia seguinte', function () {
    $usuario = Usuario::factory()->create([
        'nr_limite_busca_dia' => 1,
        'nr_buscas_hoje' => 1,
        'dt_referencia_contador' => today()->subDay(),
    ]);

    $this->actingAs($usuario)->get('/_teste-busca')->assertOk();

    expect($usuario->refresh()->nr_buscas_hoje)->toBe(1);
});
