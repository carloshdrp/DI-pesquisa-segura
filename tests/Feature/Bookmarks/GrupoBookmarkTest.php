<?php

use App\Models\Bookmark;
use App\Models\GrupoBookmark;
use App\Models\Usuario;

beforeEach(function () {
    $this->usuario = Usuario::factory()->create();
});

test('visitantes nao acessam os grupos', function () {
    $this->getJson(route('grupos.index'))->assertUnauthorized();
    $this->postJson(route('grupos.store'), ['nm_grupo' => 'IA'])->assertUnauthorized();
});

test('lista apenas os grupos do usuario com a contagem de favoritos', function () {
    $grupo = GrupoBookmark::factory()->for($this->usuario, 'usuario')->create(['nm_grupo' => 'IA']);
    Bookmark::factory()->count(2)->for($this->usuario, 'usuario')
        ->create(['id_grupo_bookmark' => $grupo->id_grupo_bookmark]);
    GrupoBookmark::factory()->create(['nm_grupo' => 'De outro usuario']);

    $this->actingAs($this->usuario)
        ->getJson(route('grupos.index'))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.nm_grupo', 'IA')
        ->assertJsonPath('0.bookmarks_count', 2);
});

test('cria um grupo para o usuario autenticado', function () {
    $this->actingAs($this->usuario)
        ->postJson(route('grupos.store'), ['nm_grupo' => 'Redes'])
        ->assertCreated()
        ->assertJsonPath('nm_grupo', 'Redes');

    expect($this->usuario->gruposBookmark()->where('nm_grupo', 'Redes')->exists())->toBeTrue();
});

test('valida o nome do grupo', function (mixed $nome) {
    $this->actingAs($this->usuario)
        ->postJson(route('grupos.store'), ['nm_grupo' => $nome])
        ->assertJsonValidationErrors('nm_grupo');
})->with([[null], [''], [str_repeat('a', 151)]]);

test('nao permite dois grupos com o mesmo nome para o mesmo usuario', function () {
    GrupoBookmark::factory()->for($this->usuario, 'usuario')->create(['nm_grupo' => 'IA']);

    $this->actingAs($this->usuario)
        ->postJson(route('grupos.store'), ['nm_grupo' => 'IA'])
        ->assertJsonValidationErrors('nm_grupo');
});

test('outro usuario pode usar o mesmo nome de grupo', function () {
    GrupoBookmark::factory()->create(['nm_grupo' => 'IA']);

    $this->actingAs($this->usuario)
        ->postJson(route('grupos.store'), ['nm_grupo' => 'IA'])
        ->assertCreated();
});

test('renomeia um grupo, inclusive mantendo o proprio nome', function () {
    $grupo = GrupoBookmark::factory()->for($this->usuario, 'usuario')->create(['nm_grupo' => 'IA']);

    $this->actingAs($this->usuario)
        ->putJson(route('grupos.update', $grupo), ['nm_grupo' => 'IA'])
        ->assertOk();

    $this->putJson(route('grupos.update', $grupo), ['nm_grupo' => 'Inteligencia Artificial'])
        ->assertOk();

    expect($grupo->refresh()->nm_grupo)->toBe('Inteligencia Artificial');
});

test('nao renomeia nem exclui grupo de outro usuario', function () {
    $grupo = GrupoBookmark::factory()->create(['nm_grupo' => 'Alheio']);

    $this->actingAs($this->usuario)
        ->putJson(route('grupos.update', $grupo), ['nm_grupo' => 'Meu'])
        ->assertForbidden();

    $this->deleteJson(route('grupos.destroy', $grupo))->assertForbidden();

    expect($grupo->fresh()->nm_grupo)->toBe('Alheio');
});

test('exclui o grupo mantendo os favoritos sem grupo', function () {
    $grupo = GrupoBookmark::factory()->for($this->usuario, 'usuario')->create();
    $bookmark = Bookmark::factory()->for($this->usuario, 'usuario')
        ->create(['id_grupo_bookmark' => $grupo->id_grupo_bookmark]);

    $this->actingAs($this->usuario)
        ->deleteJson(route('grupos.destroy', $grupo))
        ->assertNoContent();

    expect(GrupoBookmark::count())->toBe(0)
        ->and($bookmark->fresh()->id_grupo_bookmark)->toBeNull();
});

test('move um favorito para um grupo e o remove com null', function () {
    $grupo = GrupoBookmark::factory()->for($this->usuario, 'usuario')->create();
    $bookmark = Bookmark::factory()->for($this->usuario, 'usuario')->create();

    $this->actingAs($this->usuario)
        ->patchJson(route('bookmarks.grupo.update', $bookmark), [
            'id_grupo_bookmark' => $grupo->id_grupo_bookmark,
        ])
        ->assertOk();

    expect($bookmark->refresh()->id_grupo_bookmark)->toBe($grupo->id_grupo_bookmark);

    $this->patchJson(route('bookmarks.grupo.update', $bookmark), ['id_grupo_bookmark' => null])
        ->assertOk();

    expect($bookmark->refresh()->id_grupo_bookmark)->toBeNull();
});

test('nao move favorito para grupo de outro usuario', function () {
    $grupoAlheio = GrupoBookmark::factory()->create();
    $bookmark = Bookmark::factory()->for($this->usuario, 'usuario')->create();

    $this->actingAs($this->usuario)
        ->patchJson(route('bookmarks.grupo.update', $bookmark), [
            'id_grupo_bookmark' => $grupoAlheio->id_grupo_bookmark,
        ])
        ->assertJsonValidationErrors('id_grupo_bookmark');

    expect($bookmark->fresh()->id_grupo_bookmark)->toBeNull();
});

test('nao move favorito de outro usuario', function () {
    $grupo = GrupoBookmark::factory()->for($this->usuario, 'usuario')->create();
    $bookmarkAlheio = Bookmark::factory()->create();

    $this->actingAs($this->usuario)
        ->patchJson(route('bookmarks.grupo.update', $bookmarkAlheio), [
            'id_grupo_bookmark' => $grupo->id_grupo_bookmark,
        ])
        ->assertForbidden();
});
