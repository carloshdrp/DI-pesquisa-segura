<?php

use App\Models\Bookmark;
use App\Models\GrupoBookmark;
use App\Models\LinkCompartilhamento;
use App\Models\PaginaIndexada;
use App\Models\Usuario;

beforeEach(function () {
    $this->usuario = Usuario::factory()->create();
});

test('visitantes nao criam nem revogam links', function () {
    $this->postJson(route('compartilhamentos.store'), ['id_bookmark' => 1])->assertUnauthorized();

    $link = LinkCompartilhamento::factory()->create();
    $this->deleteJson(route('compartilhamentos.destroy', $link))->assertUnauthorized();
});

test('cria um link para um favorito do usuario', function () {
    $bookmark = Bookmark::factory()->for($this->usuario, 'usuario')->create();

    $resposta = $this->actingAs($this->usuario)
        ->postJson(route('compartilhamentos.store'), ['id_bookmark' => $bookmark->id_bookmark])
        ->assertCreated();

    $link = LinkCompartilhamento::sole();

    expect($link->ds_token)->toHaveLength(64)
        ->and($link->id_bookmark)->toBe($bookmark->id_bookmark)
        ->and($link->id_grupo_bookmark)->toBeNull()
        ->and($resposta->json('url'))->toBe(route('compartilhado.show', $link->ds_token));
});

test('cria um link para um grupo do usuario', function () {
    $grupo = GrupoBookmark::factory()->for($this->usuario, 'usuario')->create();

    $this->actingAs($this->usuario)
        ->postJson(route('compartilhamentos.store'), ['id_grupo_bookmark' => $grupo->id_grupo_bookmark])
        ->assertCreated();

    expect(LinkCompartilhamento::sole()->id_grupo_bookmark)->toBe($grupo->id_grupo_bookmark);
});

test('exige exatamente um alvo e que ele seja do usuario', function (Closure $payload) {
    $meuBookmark = Bookmark::factory()->for($this->usuario, 'usuario')->create();
    $meuGrupo = GrupoBookmark::factory()->for($this->usuario, 'usuario')->create();
    $bookmarkAlheio = Bookmark::factory()->create();
    $grupoAlheio = GrupoBookmark::factory()->create();

    $this->actingAs($this->usuario)
        ->postJson(route('compartilhamentos.store'), $payload($meuBookmark, $meuGrupo, $bookmarkAlheio, $grupoAlheio))
        ->assertUnprocessable();

    expect(LinkCompartilhamento::count())->toBe(0);
})->with([
    'nenhum alvo' => [fn () => []],
    'favorito e grupo juntos' => [fn ($b, $g) => ['id_bookmark' => $b->id_bookmark, 'id_grupo_bookmark' => $g->id_grupo_bookmark]],
    'favorito de outro usuario' => [fn ($b, $g, $ba) => ['id_bookmark' => $ba->id_bookmark]],
    'grupo de outro usuario' => [fn ($b, $g, $ba, $ga) => ['id_grupo_bookmark' => $ga->id_grupo_bookmark]],
]);

test('o link publico abre sem login e mostra o favorito com link para a origem', function () {
    $pagina = PaginaIndexada::factory()->create([
        'ds_titulo' => 'Artigo sobre redes',
        'ds_url' => 'https://origem.example.org/artigo',
    ]);
    $bookmark = Bookmark::factory()->create(['id_pagina_indexada' => $pagina->id_pagina_indexada]);
    $link = LinkCompartilhamento::factory()->create(['id_bookmark' => $bookmark->id_bookmark]);

    $this->get(route('compartilhado.show', $link->ds_token))
        ->assertOk()
        ->assertSee('Artigo sobre redes')
        ->assertSee('https://origem.example.org/artigo', false);
});

test('o link publico de um grupo lista os favoritos do grupo', function () {
    $grupo = GrupoBookmark::factory()->create(['nm_grupo' => 'Estudos']);
    $paginas = PaginaIndexada::factory()->count(2)->create();

    foreach ($paginas as $pagina) {
        Bookmark::factory()->create([
            'id_usuario' => $grupo->id_usuario,
            'id_pagina_indexada' => $pagina->id_pagina_indexada,
            'id_grupo_bookmark' => $grupo->id_grupo_bookmark,
        ]);
    }

    $link = LinkCompartilhamento::factory()->create([
        'id_bookmark' => null,
        'id_grupo_bookmark' => $grupo->id_grupo_bookmark,
    ]);

    $this->get(route('compartilhado.show', $link->ds_token))
        ->assertOk()
        ->assertSee('Estudos')
        ->assertSee($paginas[0]->ds_titulo)
        ->assertSee($paginas[1]->ds_titulo);
});

test('token inexistente devolve 404', function () {
    $this->get(route('compartilhado.show', str_repeat('x', 64)))->assertNotFound();
});

test('o dono revoga o link e ele deixa de abrir', function () {
    $bookmark = Bookmark::factory()->for($this->usuario, 'usuario')->create();
    $link = LinkCompartilhamento::factory()->create(['id_bookmark' => $bookmark->id_bookmark]);

    $this->get(route('compartilhado.show', $link->ds_token))->assertOk();

    $this->actingAs($this->usuario)
        ->deleteJson(route('compartilhamentos.destroy', $link))
        ->assertNoContent();

    expect($link->refresh()->estaRevogado())->toBeTrue();

    $this->get(route('compartilhado.show', $link->ds_token))->assertNotFound();
});

test('revogar duas vezes nao altera a data da primeira revogacao', function () {
    $bookmark = Bookmark::factory()->for($this->usuario, 'usuario')->create();
    $link = LinkCompartilhamento::factory()->revogado()->create(['id_bookmark' => $bookmark->id_bookmark]);
    $primeira = $link->dt_revogacao;

    $this->travel(1)->hour();

    $this->actingAs($this->usuario)
        ->deleteJson(route('compartilhamentos.destroy', $link))
        ->assertNoContent();

    expect($link->refresh()->dt_revogacao->equalTo($primeira))->toBeTrue();
});

test('outro usuario nao revoga o link', function () {
    $link = LinkCompartilhamento::factory()->create();

    $this->actingAs($this->usuario)
        ->deleteJson(route('compartilhamentos.destroy', $link))
        ->assertForbidden();

    expect($link->refresh()->estaRevogado())->toBeFalse();
});
