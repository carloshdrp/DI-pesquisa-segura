<?php

namespace App\Http\Controllers;

use App\Models\PaginaIndexada;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    /**
     * Favoritos do usuário, do mais recente ao mais antigo (RF07). Com
     * `?grupo=<id>` lista só os de um grupo.
     */
    public function index(Request $request): JsonResponse
    {
        $favoritos = $request->user()
            ->bookmarks()
            ->with('paginaIndexada.fonte')
            ->when($request->integer('grupo') ?: null, fn ($consulta, $grupo) => $consulta->where('id_grupo_bookmark', $grupo))
            ->orderByDesc('dt_criacao')
            ->orderByDesc('id_bookmark')
            ->paginate((int) config('busca.por_pagina'));

        return response()->json($favoritos);
    }

    /**
     * Favorita o resultado. Idempotente: favoritar de novo devolve o favorito
     * existente (200); o primeiro favorito responde 201.
     */
    public function favoritar(Request $request, PaginaIndexada $pagina): JsonResponse|RedirectResponse
    {
        $favorito = $request->user()->bookmarks()->firstOrCreate(
            ['id_pagina_indexada' => $pagina->id_pagina_indexada],
            ['dt_criacao' => now()],
        );

        return $request->wantsJson()
            ? response()->json($favorito, $favorito->wasRecentlyCreated ? 201 : 200)
            : back()->with('status', __('Resultado favoritado.'));
    }

    /**
     * Remove o favorito. Idempotente: se não existir, também responde com sucesso.
     */
    public function desfavoritar(Request $request, PaginaIndexada $pagina): JsonResponse|RedirectResponse
    {
        $request->user()->bookmarks()
            ->where('id_pagina_indexada', $pagina->id_pagina_indexada)
            ->delete();

        return $request->wantsJson()
            ? response()->json(null, 204)
            : back()->with('status', __('Favorito removido.'));
    }
}
