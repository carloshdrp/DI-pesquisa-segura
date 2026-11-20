<?php

use App\Models\Busca;
use App\Models\Usuario;

beforeEach(function () {
    $this->usuario = Usuario::factory()->create();
});

test('visitantes nao acessam o historico', function () {
    $this->getJson(route('historico.index'))->assertUnauthorized();
    $this->deleteJson(route('historico.limpar'))->assertUnauthorized();
    $this->deleteJson(route('historico.destroy', Busca::factory()->create()))->assertUnauthorized();
});

test('lista so as buscas do usuario, da mais recente para a mais antiga', function () {
    $antiga = Busca::factory()->for($this->usuario, 'usuario')->create(['dt_busca' => now()->subDay()]);
    $recente = Busca::factory()->for($this->usuario, 'usuario')->create(['dt_busca' => now()]);
    Busca::factory()->create();

    $resposta = $this->actingAs($this->usuario)->getJson(route('historico.index'))->assertOk();

    expect($resposta->json('data.*.id_busca'))->toBe([$recente->id_busca, $antiga->id_busca])
        ->and($resposta->json('total'))->toBe(2);
});

test('o historico e paginado', function () {
    config(['busca.por_pagina' => 2]);
    Busca::factory()->count(3)->for($this->usuario, 'usuario')->create();

    $this->actingAs($this->usuario)->getJson(route('historico.index'))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('last_page', 2);
});

test('exclui um item do proprio historico', function () {
    $busca = Busca::factory()->for($this->usuario, 'usuario')->create();

    $this->actingAs($this->usuario)->deleteJson(route('historico.destroy', $busca))->assertNoContent();

    expect(Busca::count())->toBe(0);
});

test('nao exclui a busca de outro usuario', function () {
    $busca = Busca::factory()->create();

    $this->actingAs($this->usuario)->deleteJson(route('historico.destroy', $busca))->assertForbidden();

    expect(Busca::count())->toBe(1);
});

test('limpa so o historico do usuario', function () {
    Busca::factory()->count(2)->for($this->usuario, 'usuario')->create();
    $alheia = Busca::factory()->create();

    $this->actingAs($this->usuario)->deleteJson(route('historico.limpar'))->assertNoContent();

    expect(Busca::sole()->id_busca)->toBe($alheia->id_busca);
});

test('sem JSON, excluir e limpar redirecionam de volta', function () {
    $busca = Busca::factory()->for($this->usuario, 'usuario')->create();

    $this->actingAs($this->usuario)->from('/dashboard')->delete(route('historico.destroy', $busca))->assertRedirect('/dashboard');
    $this->actingAs($this->usuario)->from('/dashboard')->delete(route('historico.limpar'))->assertRedirect('/dashboard');
});
