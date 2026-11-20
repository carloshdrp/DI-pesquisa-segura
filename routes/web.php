<?php

use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\BookmarkGrupoController;
use App\Http\Controllers\BuscaController;
use App\Http\Controllers\CompartilhadoController;
use App\Http\Controllers\GrupoBookmarkController;
use App\Http\Controllers\HistoricoController;
use App\Http\Controllers\LinkCompartilhamentoController;
use App\Http\Controllers\TopicoController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('busca', [BuscaController::class, 'index'])
    ->middleware('limite.busca')
    ->name('busca.index');

Route::get('topicos', TopicoController::class)->name('topicos.index');

Route::get('compartilhado/{token}', [CompartilhadoController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('compartilhado.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::resource('grupos', GrupoBookmarkController::class)
        ->parameters(['grupos' => 'grupo'])
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');
    Route::put('bookmarks/{pagina}', [BookmarkController::class, 'favoritar'])->name('bookmarks.favoritar');
    Route::delete('bookmarks/{pagina}', [BookmarkController::class, 'desfavoritar'])->name('bookmarks.desfavoritar');

    Route::patch('bookmarks/{bookmark}/grupo', [BookmarkGrupoController::class, 'update'])
        ->name('bookmarks.grupo.update');

    Route::get('historico', [HistoricoController::class, 'index'])->name('historico.index');
    Route::delete('historico', [HistoricoController::class, 'limpar'])->name('historico.limpar');
    Route::delete('historico/{busca}', [HistoricoController::class, 'destroy'])->name('historico.destroy');

    Route::post('compartilhamentos', [LinkCompartilhamentoController::class, 'store'])
        ->name('compartilhamentos.store');
    Route::delete('compartilhamentos/{link}', [LinkCompartilhamentoController::class, 'destroy'])
        ->name('compartilhamentos.destroy');
});

require __DIR__.'/settings.php';
