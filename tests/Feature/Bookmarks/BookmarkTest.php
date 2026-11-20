<?php

use App\Models\Bookmark;
use App\Models\GrupoBookmark;
use App\Models\PaginaIndexada;
use App\Models\Usuario;

beforeEach(function () {
    $this->usuario = Usuario::factory()->create();
    $this->pagina = PaginaIndexada::factory()->create();
});

test('visitantes nao favoritam nem listam favoritos', function () {
    $this->putJson(route('bookmarks.favoritar', $this->pagina))->assertUnauthorized();
    $this->deleteJson(route('bookmarks.desfavoritar', $this->pagina))->assertUnauthorized();
    $this->getJson(route('bookmarks.index'))->assertUnauthorized();
});

test('favorita um resultado', function () {
    $this->actingAs($this->usuario)
        ->putJson(route('bookmarks.favoritar', $this->pagina))
        ->assertCreated();

    $favorito = Bookmark::sole();

    expect($favorito->id_usuario)->toBe($this->usuario->id_usuario)
        ->and($favorito->id_pagina_indexada)->toBe($this->pagina->id_pagina_indexada)
        ->and($favorito->id_grupo_bookmark)->toBeNull();
});

test('favoritar de novo e idempotente', function () {
    $this->actingAs($this->usuario)->putJson(route('bookmarks.favoritar', $this->pagina))->assertCreated();
    $this->actingAs($this->usuario)->putJson(route('bookmarks.favoritar', $this->pagina))->assertOk();

    expect(Bookmark::count())->toBe(1);
});

test('dois usuarios podem favoritar a mesma pagina', function () {
    $outro = Usuario::factory()->create();

    $this->actingAs($this->usuario)->putJson(route('bookmarks.favoritar', $this->pagina))->assertCreated();
    $this->actingAs($outro)->putJson(route('bookmarks.favoritar', $this->pagina))->assertCreated();

    expect(Bookmark::count())->toBe(2);
});

test('pagina inexistente devolve 404', function () {
    $this->actingAs($this->usuario)->putJson(route('bookmarks.favoritar', 999999))->assertNotFound();
});

test('desfavorita so o favorito do proprio usuario e e idempotente', function () {
    Bookmark::factory()->for($this->usuario, 'usuario')->for($this->pagina, 'paginaIndexada')->create();
    $alheio = Bookmark::factory()->for($this->pagina, 'paginaIndexada')->create();

    $this->actingAs($this->usuario)->deleteJson(route('bookmarks.desfavoritar', $this->pagina))->assertNoContent();
    $this->actingAs($this->usuario)->deleteJson(route('bookmarks.desfavoritar', $this->pagina))->assertNoContent();

    expect(Bookmark::sole()->id_bookmark)->toBe($alheio->id_bookmark);
});

test('lista os favoritos do usuario com a pagina e a fonte, e filtra por grupo', function () {
    $grupo = GrupoBookmark::factory()->for($this->usuario, 'usuario')->create();
    $noGrupo = Bookmark::factory()->for($this->usuario, 'usuario')->create(['id_grupo_bookmark' => $grupo->id_grupo_bookmark]);
    Bookmark::factory()->for($this->usuario, 'usuario')->create();
    Bookmark::factory()->create();

    $todos = $this->actingAs($this->usuario)->getJson(route('bookmarks.index'))->assertOk();
    $filtrado = $this->actingAs($this->usuario)->getJson(route('bookmarks.index', ['grupo' => $grupo->id_grupo_bookmark]))->assertOk();

    expect($todos->json('total'))->toBe(2)
        ->and($todos->json('data.0.pagina_indexada.fonte'))->not->toBeNull()
        ->and($filtrado->json('data.*.id_bookmark'))->toBe([$noGrupo->id_bookmark]);
});

test('sem JSON, favoritar e desfavoritar redirecionam de volta', function () {
    $this->actingAs($this->usuario)->from('/busca')->put(route('bookmarks.favoritar', $this->pagina))->assertRedirect('/busca');
    $this->actingAs($this->usuario)->from('/busca')->delete(route('bookmarks.desfavoritar', $this->pagina))->assertRedirect('/busca');
});
