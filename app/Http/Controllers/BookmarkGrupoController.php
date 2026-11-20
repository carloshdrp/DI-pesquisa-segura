<?php

namespace App\Http\Controllers;

use App\Http\Requests\MoverBookmarkRequest;
use App\Models\Bookmark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class BookmarkGrupoController extends Controller
{
    /**
     * Move o favorito para um grupo, ou o tira de qualquer grupo com null.
     */
    public function update(MoverBookmarkRequest $request, Bookmark $bookmark): JsonResponse|RedirectResponse
    {
        Gate::authorize('update', $bookmark);

        $bookmark->update([
            'id_grupo_bookmark' => $request->validated('id_grupo_bookmark'),
        ]);

        return $request->wantsJson()
            ? response()->json($bookmark)
            : back()->with('status', __('Favorito atualizado.'));
    }
}
