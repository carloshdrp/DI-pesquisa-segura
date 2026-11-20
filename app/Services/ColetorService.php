<?php

namespace App\Services;

use App\Exceptions\FonteNaoColetavelException;
use App\Models\Fonte;
use App\Models\PaginaIndexada;
use Illuminate\Support\Str;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlResponse;

class ColetorService
{
    /**
     * @param  array<string, string>|null  $respostasFake  usado nos testes, no lugar de requisições reais
     */
    public function __construct(private readonly ?array $respostasFake = null) {}

    /**
     * Percorre o site da fonte, respeitando o robots.txt, e indexa cada página.
     *
     * @return int quantidade de páginas salvas
     *
     * @throws FonteNaoColetavelException
     */
    public function coletar(Fonte $fonte): int
    {
        if (! $fonte->fl_ativa || ! $fonte->fl_robots_permitido) {
            throw FonteNaoColetavelException::para($fonte);
        }

        $salvas = 0;
        $config = (array) config('busca.coleta');

        $crawler = Crawler::create($fonte->ds_url_base)
            ->userAgent((string) $config['user_agent'])
            ->respectRobots()
            ->internalOnly()
            ->depth((int) $config['profundidade'])
            ->limit((int) $config['max_paginas'])
            ->delay((int) $config['atraso_ms'])
            ->maxResponseSizeInBytes((int) $config['tamanho_max_bytes'])
            ->allowedMimeTypes(['text/html'])
            ->onCrawled(function (string $url, CrawlResponse $response) use ($fonte, &$salvas): void {
                if ($response->isSuccessful() && $this->salvar($fonte, $url, $response)) {
                    $salvas++;
                }
            });

        if ($this->respostasFake !== null) {
            $crawler->fake($this->respostasFake);
        }

        $crawler->start();

        return $salvas;
    }

    /**
     * Salva ou atualiza a página. Páginas com noindex e URLs longas demais são ignoradas.
     */
    private function salvar(Fonte $fonte, string $url, CrawlResponse $response): bool
    {
        $url = Str::before($url, '#');

        if (mb_strlen($url) > 255) {
            return false;
        }

        $dom = $response->dom();

        $robots = $dom->filter('meta[name="robots"]');
        if ($robots->count() > 0 && str_contains(strtolower((string) $robots->attr('content')), 'noindex')) {
            return false;
        }

        $titulo = $dom->filter('title');
        $descricao = $dom->filter('meta[name="description"]');

        $dom->filter('script, style, noscript')->each(function ($no): void {
            $node = $no->getNode(0);
            $node?->parentNode?->removeChild($node);
        });

        $corpo = $dom->filter('body');
        $texto = Str::of($corpo->count() > 0 ? $corpo->text('') : '')
            ->squish()
            ->limit((int) config('busca.coleta.conteudo_max_caracteres'), '')
            ->toString();

        $titulo = trim($titulo->count() > 0 ? $titulo->text('') : '');
        $resumo = trim($descricao->count() > 0 ? (string) $descricao->attr('content') : '');

        $pagina = PaginaIndexada::firstOrNew(['ds_url' => $url]);

        $pagina->fill([
            'ds_titulo' => Str::limit($titulo !== '' ? $titulo : $url, 255, ''),
            'ds_resumo' => $resumo !== '' ? $resumo : Str::limit($texto, 300, ''),
            'ds_conteudo_texto' => $texto,
            'dt_atualizado' => now(),
            'id_fonte' => $fonte->id_fonte,
        ]);

        if (! $pagina->exists) {
            $pagina->dt_coletado = now();
        }

        $pagina->save();

        return true;
    }
}
