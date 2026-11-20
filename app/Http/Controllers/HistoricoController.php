<?php

namespace App\Http\Controllers;

use App\Models\Busca;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class HistoricoController extends Controller
{
    /**
     * Histórico do usuário (RF10), da busca mais recente para a mais antiga.
     */
    public function index(Request $request): JsonResponse
    {
        $buscas = Busca::where('id_usuario', $request->user()->id_usuario)
            ->orderByDesc('dt_busca')
            ->orderByDesc('id_busca')
            ->paginate((int) config('busca.por_pagina'));

        return response()->json($buscas);
    }

    public function destroy(Request $request, Busca $busca): JsonResponse|RedirectResponse
    {
        Gate::authorize('delete', $busca);

        $busca->delete();

        return $request->wantsJson()
            ? response()->json(null, 204)
            : back()->with('status', __('Busca removida do histórico.'));
    }

    /**
     * Limpa todo o histórico do usuário (RF11).
     */
    public function limpar(Request $request): JsonResponse|RedirectResponse
    {
        Busca::where('id_usuario', $request->user()->id_usuario)->delete();

        return $request->wantsJson()
            ? response()->json(null, 204)
            : back()->with('status', __('Histórico limpo.'));
    }
}
