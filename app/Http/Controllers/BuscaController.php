<?php

namespace App\Http\Controllers;

use App\Http\Middleware\LimitarBuscasDiarias;
use App\Http\Requests\BuscaRequest;
use App\Http\Resources\ResultadoBuscaResource;
use App\Models\Busca;
use App\Services\ColetaPorTermoService;
use App\Services\PesquisaService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BuscaController extends Controller
{
    public function __construct(
        private readonly PesquisaService $pesquisa,
        private readonly ColetaPorTermoService $coleta,
    ) {}

    /**
     * Busca pública (RF02). A resposta é calculada só com o índice local, sem
     * esperar fontes externas (RNF04); se houver poucos resultados, uma nova
     * coleta é pedida em segundo plano para as próximas buscas (RF13).
     */
    public function index(BuscaRequest $request): AnonymousResourceCollection
    {
        $termo = (string) $request->validated('q');
        $resultados = $this->pesquisa->pesquisar($termo);

        // Só a primeira página é uma busca nova.
        if ($resultados->currentPage() === 1) {
            Busca::create([
                'ds_termo' => $termo,
                'nr_resultados' => $resultados->total(),
                'dt_busca' => now(),
                'id_usuario' => $request->user()?->id_usuario,
                'id_sessao_anonima' => $request->attributes->get(LimitarBuscasDiarias::ATRIBUTO_SESSAO)?->id_sessao_anonima,
            ]);

            if ($resultados->total() < (int) config('busca.minimo_resultados')) {
                $this->coleta->solicitar($termo);
            }
        }

        return ResultadoBuscaResource::collection($resultados)->additional(['termo' => $termo]);
    }
}
