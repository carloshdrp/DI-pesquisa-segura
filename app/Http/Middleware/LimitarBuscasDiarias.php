<?php

namespace App\Http\Middleware;

use App\Models\SessaoAnonima;
use App\Services\LimiteBuscaService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LimitarBuscasDiarias
{
    public const COOKIE_SESSAO = 'sessao_anonima';

    /** Atributo da requisição com a SessaoAnonima do visitante (ou null). */
    public const ATRIBUTO_SESSAO = 'sessao_anonima';

    public function __construct(private readonly LimiteBuscaService $limites) {}

    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();
        $sessao = $usuario ? null : $this->sessaoAnonima($request);

        $request->attributes->set(self::ATRIBUTO_SESSAO, $sessao);

        // Navegar pelas páginas de uma busca já feita não gasta outra busca do dia.
        $consome = $request->integer('page', 1) <= 1;

        if ($consome && ! $this->limites->consumir($usuario ?? $sessao)) {
            throw new HttpException(
                429,
                __('Você atingiu o limite diário de buscas. Tente novamente amanhã.'),
                headers: ['Retry-After' => $this->limites->segundosAteReset()],
            );
        }

        $response = $next($request);

        if ($sessao) {
            $response->headers->setCookie(
                Cookie::forever(self::COOKIE_SESSAO, $sessao->ds_identificador)
            );
        }

        return $response;
    }

    private function sessaoAnonima(Request $request): SessaoAnonima
    {
        $identificador = $request->cookie(self::COOKIE_SESSAO);

        if (is_string($identificador) && Str::isUuid($identificador)) {
            $sessao = SessaoAnonima::where('ds_identificador', $identificador)->first();

            if ($sessao) {
                return $sessao;
            }
        }

        return SessaoAnonima::create([
            'ds_identificador' => (string) Str::uuid(),
            'nr_buscas_hoje' => 0,
            'dt_referencia_contador' => today(),
            'dt_criacao' => now(),
        ]);
    }
}
