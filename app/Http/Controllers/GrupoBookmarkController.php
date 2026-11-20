<?php

namespace App\Http\Controllers;

use App\Http\Requests\GrupoBookmarkRequest;
use App\Models\GrupoBookmark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GrupoBookmarkController extends Controller
{
    /**
     * Lista os grupos do usuário com a quantidade de favoritos de cada um.
     */
    public function index(Request $request): JsonResponse
    {
        $grupos = $request->user()
            ->gruposBookmark()
            ->withCount('bookmarks')
            ->orderBy('nm_grupo')
            ->get();

        return response()->json($grupos);
    }

    public function store(GrupoBookmarkRequest $request): JsonResponse|RedirectResponse
    {
        $grupo = $request->user()->gruposBookmark()->create([
            'nm_grupo' => $request->validated('nm_grupo'),
            'dt_criacao' => now(),
        ]);

        return $request->wantsJson()
            ? response()->json($grupo, 201)
            : back()->with('status', __('Grupo criado.'));
    }

    public function update(GrupoBookmarkRequest $request, GrupoBookmark $grupo): JsonResponse|RedirectResponse
    {
        Gate::authorize('update', $grupo);

        $grupo->update(['nm_grupo' => $request->validated('nm_grupo')]);

        return $request->wantsJson()
            ? response()->json($grupo)
            : back()->with('status', __('Grupo atualizado.'));
    }

    /**
     * Os favoritos do grupo excluído continuam salvos, sem grupo.
     */
    public function destroy(Request $request, GrupoBookmark $grupo): JsonResponse|RedirectResponse
    {
        Gate::authorize('delete', $grupo);

        $grupo->delete();

        return $request->wantsJson()
            ? response()->json(null, 204)
            : back()->with('status', __('Grupo excluído.'));
    }
}
