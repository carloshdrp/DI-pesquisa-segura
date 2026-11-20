<?php

use App\Http\Requests\BuscaRequest;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    config(['busca.termos_bloqueados' => ['pornografia']]);

    Route::middleware('web')->get('/_teste-filtro', fn (BuscaRequest $request) => $request->validated('q'));
});

test('aceita uma busca permitida e remove espacos nas pontas', function () {
    $this->getJson('/_teste-filtro?'.http_build_query(['q' => '  inteligencia artificial ']))
        ->assertOk()
        ->assertSee('inteligencia artificial');
});

test('recusa uma busca com termo bloqueado', function () {
    $this->getJson('/_teste-filtro?q=Pornografia')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['q' => 'Esta busca não é permitida.']);
});

test('valida tamanho e obrigatoriedade do termo', function (?string $q) {
    $this->getJson('/_teste-filtro'.($q === null ? '' : '?q='.urlencode($q)))
        ->assertJsonValidationErrors('q');
})->with([[null], [''], ['a'], [str_repeat('a', 201)]]);
