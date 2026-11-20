<?php

use App\Services\FiltroBuscaService;

beforeEach(function () {
    config(['busca.termos_bloqueados' => ['pornografia', 'sexo explícito', 'xxx']]);

    $this->filtro = new FiltroBuscaService;
});

test('permite buscas comuns', function (string $termo) {
    expect($this->filtro->permitido($termo))->toBeTrue();
})->with(['machine learning', 'redes neurais convolucionais', 'história do Brasil']);

test('bloqueia termos proibidos ignorando maiusculas, acentos e pontuacao', function (string $termo) {
    expect($this->filtro->permitido($termo))->toBeFalse();
})->with([
    'pornografia',
    'PORNOGRAFIA',
    'sexo explicito',
    'Sexo   Explícito!',
    'artigos sobre pornografia infantil',
]);

test('so considera palavras inteiras', function () {
    expect($this->filtro->permitido('anxxxiety'))->toBeTrue()
        ->and($this->filtro->permitido('pornografiazinha'))->toBeTrue();
});

test('sem termos configurados tudo e permitido', function () {
    config(['busca.termos_bloqueados' => []]);

    expect($this->filtro->permitido('pornografia'))->toBeTrue();
});

test('normaliza o texto', function () {
    expect($this->filtro->normalizar('  Ação & Reação!  '))->toBe('acao reacao');
});
