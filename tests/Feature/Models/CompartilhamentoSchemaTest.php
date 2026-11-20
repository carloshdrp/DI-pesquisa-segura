<?php

use App\Models\Bookmark;
use App\Models\GrupoBookmark;
use App\Models\LinkCompartilhamento;
use App\Models\Usuario;
use Illuminate\Database\QueryException;

test('um favorito pode ser organizado em um grupo do mesmo usuario', function () {
    $usuario = Usuario::factory()->create();
    $grupo = GrupoBookmark::factory()->for($usuario, 'usuario')->create();

    $bookmark = Bookmark::factory()
        ->for($usuario, 'usuario')
        ->create(['id_grupo_bookmark' => $grupo->id_grupo_bookmark]);

    expect($bookmark->grupo->is($grupo))->toBeTrue()
        ->and($grupo->bookmarks)->toHaveCount(1)
        ->and($usuario->gruposBookmark)->toHaveCount(1);
});

test('o nome do grupo e unico por usuario', function () {
    $usuario = Usuario::factory()->create();
    GrupoBookmark::factory()->for($usuario, 'usuario')->create(['nm_grupo' => 'IA']);

    GrupoBookmark::factory()->for($usuario, 'usuario')->create(['nm_grupo' => 'IA']);
})->throws(QueryException::class);

test('usuarios diferentes podem ter grupos com o mesmo nome', function () {
    GrupoBookmark::factory()->create(['nm_grupo' => 'IA']);
    GrupoBookmark::factory()->create(['nm_grupo' => 'IA']);

    expect(GrupoBookmark::where('nm_grupo', 'IA')->count())->toBe(2);
});

test('excluir o grupo mantem os favoritos sem grupo', function () {
    $grupo = GrupoBookmark::factory()->create();
    $bookmark = Bookmark::factory()->create(['id_grupo_bookmark' => $grupo->id_grupo_bookmark]);

    $grupo->delete();

    expect($bookmark->fresh())->not->toBeNull()
        ->and($bookmark->fresh()->id_grupo_bookmark)->toBeNull();
});

test('um link pode compartilhar um favorito', function () {
    $link = LinkCompartilhamento::factory()->create();

    expect($link->bookmark)->not->toBeNull()
        ->and($link->grupo)->toBeNull()
        ->and($link->estaRevogado())->toBeFalse();
});

test('um link pode compartilhar um grupo', function () {
    $grupo = GrupoBookmark::factory()->create();

    $link = LinkCompartilhamento::factory()->create([
        'id_bookmark' => null,
        'id_grupo_bookmark' => $grupo->id_grupo_bookmark,
    ]);

    expect($link->grupo->is($grupo))->toBeTrue()
        ->and($grupo->links)->toHaveCount(1);
});

test('um link revogado fica marcado como revogado', function () {
    $link = LinkCompartilhamento::factory()->revogado()->create();

    expect($link->estaRevogado())->toBeTrue();
});

test('um link nao pode apontar para favorito e grupo ao mesmo tempo', function () {
    $bookmark = Bookmark::factory()->create();
    $grupo = GrupoBookmark::factory()->create();

    LinkCompartilhamento::factory()->create([
        'id_bookmark' => $bookmark->id_bookmark,
        'id_grupo_bookmark' => $grupo->id_grupo_bookmark,
    ]);
})->throws(QueryException::class);

test('um link precisa apontar para um favorito ou um grupo', function () {
    LinkCompartilhamento::factory()->create([
        'id_bookmark' => null,
        'id_grupo_bookmark' => null,
    ]);
})->throws(QueryException::class);

test('o token do link e unico', function () {
    LinkCompartilhamento::factory()->create(['ds_token' => str_repeat('a', 64)]);

    LinkCompartilhamento::factory()->create(['ds_token' => str_repeat('a', 64)]);
})->throws(QueryException::class);

test('excluir o favorito remove seus links de compartilhamento', function () {
    $link = LinkCompartilhamento::factory()->create();

    $link->bookmark->delete();

    expect(LinkCompartilhamento::count())->toBe(0);
});
