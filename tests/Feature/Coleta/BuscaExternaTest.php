<?php

use App\Enums\OrigemColeta;
use App\Enums\StatusColeta;
use App\Jobs\ArXivBuscaJob;
use App\Jobs\ColetarTermoJob;
use App\Jobs\CrossrefBuscaJob;
use App\Jobs\OpenAlexBuscaJob;
use App\Models\FilaColeta;
use App\Models\Fonte;
use App\Models\PaginaIndexada;
use App\Services\ArXivService;
use App\Services\CrossrefService;
use App\Services\IndexadorResultadosService;
use App\Services\OpenAlexService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

function openAlexFake(): array
{
    return ['results' => [
        [
            'id' => 'https://openalex.org/W1',
            'doi' => 'https://doi.org/10.1/redes',
            'title' => 'Redes neurais',
            'abstract_inverted_index' => ['Um' => [0], 'estudo' => [1], 'sobre' => [2], 'redes' => [3]],
        ],
        ['id' => 'https://openalex.org/W2', 'display_name' => 'Sem resumo'],
        ['id' => 'https://openalex.org/W3'],
    ]];
}

function arxivFake(): string
{
    return <<<'XML'
<feed xmlns="http://www.w3.org/2005/Atom">
  <entry>
    <id>http://arxiv.org/abs/1234.5678v1</id>
    <title>Aprendizado
      profundo</title>
    <summary>  Resumo   do artigo. </summary>
  </entry>
  <entry><id></id><title>Sem url</title></entry>
</feed>
XML;
}

function crossrefFake(): array
{
    return ['message' => ['items' => [
        ['URL' => 'https://doi.org/10.2/x', 'title' => ['Artigo X'], 'abstract' => '<jats:p>Resumo <b>X</b></jats:p>'],
        ['URL' => 'https://doi.org/10.2/y', 'title' => []],
    ]]];
}

function fonte(string $url, array $atributos = []): Fonte
{
    return Fonte::factory()->create(['ds_url_base' => $url] + $atributos);
}

test('o OpenAlex normaliza os resultados e monta o resumo', function () {
    Http::fake(['api.openalex.org/*' => Http::response(openAlexFake())]);

    $resultados = app(OpenAlexService::class)->buscar('redes');

    expect($resultados)->toHaveCount(2)
        ->and($resultados[0]->url)->toBe('https://doi.org/10.1/redes')
        ->and($resultados[0]->titulo)->toBe('Redes neurais')
        ->and($resultados[0]->resumo)->toBe('Um estudo sobre redes')
        ->and($resultados[1]->url)->toBe('https://openalex.org/W2')
        ->and($resultados[1]->resumo)->toBeNull();
});

test('o arXiv normaliza o feed Atom e descarta entradas sem url', function () {
    Http::fake(['export.arxiv.org/*' => Http::response(arxivFake())]);

    $resultados = app(ArXivService::class)->buscar('deep');

    expect($resultados)->toHaveCount(1)
        ->and($resultados[0]->url)->toBe('http://arxiv.org/abs/1234.5678v1')
        ->and($resultados[0]->titulo)->toBe('Aprendizado profundo')
        ->and($resultados[0]->resumo)->toBe('Resumo do artigo.');
});

test('o Crossref normaliza os itens, limpa o resumo e descarta itens sem titulo', function () {
    Http::fake(['api.crossref.org/*' => Http::response(crossrefFake())]);

    $resultados = app(CrossrefService::class)->buscar('x');

    expect($resultados)->toHaveCount(1)
        ->and($resultados[0]->titulo)->toBe('Artigo X')
        ->and($resultados[0]->resumo)->toBe('Resumo X');
});

test('erro da API lanca excecao para o job tentar de novo', function () {
    Http::fake(['api.crossref.org/*' => Http::response('erro', 500)]);

    app(CrossrefService::class)->buscar('x');
})->throws(RequestException::class);

test('o indexador grava os resultados na fonte, com as datas', function () {
    Http::fake(['api.openalex.org/*' => Http::response(openAlexFake())]);
    $fonte = fonte('https://openalex.org');

    $gravadas = app(IndexadorResultadosService::class)->indexar(app(OpenAlexService::class), 'redes');

    $pagina = PaginaIndexada::where('ds_url', 'https://doi.org/10.1/redes')->sole();

    expect($gravadas)->toBe(2)
        ->and($pagina->id_fonte)->toBe($fonte->id_fonte)
        ->and($pagina->ds_resumo)->toBe('Um estudo sobre redes')
        ->and($pagina->dt_coletado)->not->toBeNull()
        ->and($pagina->dt_atualizado)->not->toBeNull();
});

test('reindexar atualiza a pagina sem duplicar nem mudar dt_coletado', function () {
    Http::fake(['api.openalex.org/*' => Http::response(openAlexFake())]);
    fonte('https://openalex.org');
    $indexador = app(IndexadorResultadosService::class);

    $this->travelTo(now()->subDay());
    $indexador->indexar(app(OpenAlexService::class), 'redes');
    $coletado = PaginaIndexada::first()->dt_coletado;

    $this->travelBack();
    $indexador->indexar(app(OpenAlexService::class), 'redes');

    $pagina = PaginaIndexada::where('ds_url', 'https://doi.org/10.1/redes')->sole();

    expect(PaginaIndexada::count())->toBe(2)
        ->and($pagina->dt_coletado->equalTo($coletado))->toBeTrue()
        ->and($pagina->dt_atualizado->greaterThan($coletado))->toBeTrue();
});

test('nao indexa fonte inativa, sem robots liberado ou nao cadastrada', function (array $fonte) {
    Http::fake();

    if ($fonte !== []) {
        fonte('https://openalex.org', $fonte);
    }

    $gravadas = app(IndexadorResultadosService::class)->indexar(app(OpenAlexService::class), 'redes');

    expect($gravadas)->toBe(0)->and(PaginaIndexada::count())->toBe(0);

    Http::assertNothingSent();
})->with([
    'inativa' => [['fl_ativa' => false]],
    'sem robots' => [['fl_robots_permitido' => false]],
    'nao cadastrada' => [[]],
]);

test('os jobs de busca indexam o termo pela fila de coleta', function () {
    Http::fake([
        'api.openalex.org/*' => Http::response(openAlexFake()),
        'export.arxiv.org/*' => Http::response(arxivFake()),
        'api.crossref.org/*' => Http::response(crossrefFake()),
    ]);
    fonte('https://openalex.org');
    fonte('https://arxiv.org');
    fonte('https://www.crossref.org');

    foreach ([OpenAlexBuscaJob::class, ArXivBuscaJob::class, CrossrefBuscaJob::class] as $classe) {
        $job = new $classe('termo');
        $job->handle(app(IndexadorResultadosService::class));

        expect($job->queue)->toBe('coleta')
            ->and($job->tries)->toBe(3)
            ->and($job->timeout)->toBe(60)
            ->and($job->backoff())->toBe([30, 120]);
    }

    expect(PaginaIndexada::count())->toBe(4);
});

test('o pedido de coleta por termo tambem consulta as tres APIs', function () {
    Queue::fake();
    $fila = FilaColeta::create([
        'ds_termo_busca' => 'redes neurais',
        'tp_origem' => OrigemColeta::Busca,
        'tp_status' => StatusColeta::Pendente,
        'dt_agendamento' => now(),
    ]);

    (new ColetarTermoJob($fila->id_fila_coleta))->handle();

    foreach ([OpenAlexBuscaJob::class, ArXivBuscaJob::class, CrossrefBuscaJob::class] as $classe) {
        Queue::assertPushed($classe, fn ($job) => $job->busca === 'redes neurais');
    }
});
