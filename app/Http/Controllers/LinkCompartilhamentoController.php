<?php

namespace App\Http\Controllers;

use App\Http\Requests\CriarLinkCompartilhamentoRequest;
use App\Models\LinkCompartilhamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class LinkCompartilhamentoController extends Controller
{
    public function store(CriarLinkCompartilhamentoRequest $request): JsonResponse|RedirectResponse
    {
        $link = LinkCompartilhamento::create([
            'ds_token' => Str::random(64),
            'dt_criacao' => now(),
            'id_bookmark' => $request->validated('id_bookmark'),
            'id_grupo_bookmark' => $request->validated('id_grupo_bookmark'),
        ]);

        $url = route('compartilhado.show', $link->ds_token);

        return $request->wantsJson()
            ? response()->json(['link' => $link, 'url' => $url], 201)
            : back()->with('status', __('Link criado.'))->with('url_compartilhamento', $url);
    }

    /**
     * Revoga o link: ele deixa de abrir, mas o registro é mantido.
     */
    public function destroy(Request $request, LinkCompartilhamento $link): JsonResponse|RedirectResponse
    {
        Gate::authorize('delete', $link);

        if (! $link->estaRevogado()) {
            $link->update(['dt_revogacao' => now()]);
        }

        return $request->wantsJson()
            ? response()->json(null, 204)
            : back()->with('status', __('Link revogado.'));
    }
}
