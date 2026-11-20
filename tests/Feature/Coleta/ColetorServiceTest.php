<?php

use App\Exceptions\FonteNaoColetavelException;
use App\Models\Fonte;
use App\Models\PaginaIndexada;
use App\Services\ColetorService;

function html(string $titulo, string $corpo, string $extraHead = ''): string
{
    return "<html><head><title>{$titulo}</title>{$extraHead}</head><body>{$corpo}</body></html>";
}

beforeEach(function () {
    config(['busca.coleta.atraso_ms' => 0]);

    $this->fonte = Fonte::factory()->create(['ds_url_base' => 'https://fonte.test/']);
});

test('indexa as paginas da fonte com titulo, resumo e texto', function () {
    $coletor = new ColetorService([
        'https://fonte.test/' => html(
            'Inicio',
            '<script>var x = 1;</script><p>Bem-vindo</p><a href="/artigo">Artigo</a>',
            '<meta name="description" content="Pagina inicial">',
        ),
        'https://fonte.test/artigo' => html('Artigo sobre redes', '<p>Redes neurais   profundas.</p>'),
    ]);

    expect($coletor->coletar($this->fonte))->toBe(2);

    $inicio = PaginaIndexada::where('ds_url', 'https://fonte.test/')->sole();
    $artigo = PaginaIndexada::where('ds_url', 'https://fonte.test/artigo')->sole();

    expect($inicio->ds_titulo)->toBe('Inicio')
        ->and($inicio->ds_resumo)->toBe('Pagina inicial')
        ->and($inicio->ds_conteudo_texto)->toContain('Bem-vindo')->not->toContain('var x')
        ->and($inicio->id_fonte)->toBe($this->fonte->id_fonte)
        ->and($artigo->ds_titulo)->toBe('Artigo sobre redes')
        ->and($artigo->ds_conteudo_texto)->toBe('Redes neurais profundas.')
        ->and($artigo->ds_resumo)->toBe('Redes neurais profundas.');
});

test('respeita o robots txt da fonte', function () {
    $coletor = new ColetorService([
        'https://fonte.test/robots.txt' => "User-agent: *\nDisallow: /privado",
        'https://fonte.test/' => html('Inicio', '<a href="/privado">x</a><a href="/publico">y</a>'),
        'https://fonte.test/privado' => html('Privado', 'segredo'),
        'https://fonte.test/publico' => html('Publico', 'aberto'),
    ]);

    $coletor->coletar($this->fonte);

    expect(PaginaIndexada::pluck('ds_url')->all())
        ->toContain('https://fonte.test/publico')
        ->not->toContain('https://fonte.test/privado');
});

test('nao sai do dominio da fonte', function () {
    $coletor = new ColetorService([
        'https://fonte.test/' => html('Inicio', '<a href="https://outro.test/pagina">fora</a>'),
        'https://outro.test/pagina' => html('Fora', 'conteudo externo'),
    ]);

    $coletor->coletar($this->fonte);

    expect(PaginaIndexada::where('ds_url', 'like', 'https://outro.test%')->count())->toBe(0);
});

test('ignora paginas com noindex', function () {
    $coletor = new ColetorService([
        'https://fonte.test/' => html('Inicio', '<a href="/oculta">x</a>'),
        'https://fonte.test/oculta' => html('Oculta', 'x', '<meta name="robots" content="noindex, nofollow">'),
    ]);

    $coletor->coletar($this->fonte);

    expect(PaginaIndexada::where('ds_titulo', 'Oculta')->exists())->toBeFalse();
});

test('coletar de novo atualiza a pagina sem duplicar e mantem a data da primeira coleta', function () {
    $primeira = new ColetorService(['https://fonte.test/' => html('Titulo antigo', 'texto antigo')]);
    $primeira->coletar($this->fonte);

    $coletadoEm = PaginaIndexada::sole()->dt_coletado;

    $this->travel(1)->day();

    $segunda = new ColetorService(['https://fonte.test/' => html('Titulo novo', 'texto novo')]);
    $segunda->coletar($this->fonte);

    $pagina = PaginaIndexada::sole();

    expect($pagina->ds_titulo)->toBe('Titulo novo')
        ->and($pagina->dt_coletado->equalTo($coletadoEm))->toBeTrue()
        ->and($pagina->dt_atualizado->greaterThan($coletadoEm))->toBeTrue();
});

test('usa a url como titulo quando a pagina nao tem title', function () {
    $coletor = new ColetorService(['https://fonte.test/' => '<html><body>sem titulo</body></html>']);

    $coletor->coletar($this->fonte);

    expect(PaginaIndexada::sole()->ds_titulo)->toBe('https://fonte.test/');
});

test('nao coleta fonte inativa ou sem permissao de robots', function (array $atributos) {
    $fonte = Fonte::factory()->create($atributos);

    (new ColetorService([]))->coletar($fonte);
})->with([
    'inativa' => [['fl_ativa' => false]],
    'robots nao permitido' => [['fl_robots_permitido' => false]],
])->throws(FonteNaoColetavelException::class);

test('nao salva nada quando a fonte nao e coletavel', function () {
    $fonte = Fonte::factory()->create(['fl_ativa' => false]);

    try {
        (new ColetorService(['https://fonte.test/' => html('x', 'y')]))->coletar($fonte);
    } catch (FonteNaoColetavelException) {
    }

    expect(PaginaIndexada::count())->toBe(0);
});
