<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\LinkCompartilhamento;
use Illuminate\Contracts\View\View;

class CompartilhadoController extends Controller
{
    /**
     * Página pública e somente leitura. Link inexistente ou revogado devolve 404.
     */
    public function show(string $token): View
    {
        $link = LinkCompartilhamento::ativos()
            ->where('ds_token', $token)
            ->with(['bookmark.paginaIndexada.fonte', 'grupo.bookmarks.paginaIndexada.fonte'])
            ->firstOrFail();

        if ($link->id_bookmark !== null) {
            $pagina = $link->bookmark->paginaIndexada;

            return view('compartilhado.show', [
                'titulo' => $pagina->ds_titulo,
                'paginas' => collect([$pagina]),
            ]);
        }

        return view('compartilhado.show', [
            'titulo' => $link->grupo->nm_grupo,
            'paginas' => $link->grupo->bookmarks->map(fn (Bookmark $bookmark) => $bookmark->paginaIndexada),
        ]);
    }
}
