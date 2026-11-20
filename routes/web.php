<?php

use App\Http\Controllers\BookmarkGrupoController;
use App\Http\Controllers\CompartilhadoController;
use App\Http\Controllers\GrupoBookmarkController;
use App\Http\Controllers\LinkCompartilhamentoController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('compartilhado/{token}', [CompartilhadoController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('compartilhado.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::resource('grupos', GrupoBookmarkController::class)
        ->parameters(['grupos' => 'grupo'])
        ->only(['index', 'store', 'update', 'destroy']);

    Route::patch('bookmarks/{bookmark}/grupo', [BookmarkGrupoController::class, 'update'])
        ->name('bookmarks.grupo.update');

    Route::post('compartilhamentos', [LinkCompartilhamentoController::class, 'store'])
        ->name('compartilhamentos.store');
    Route::delete('compartilhamentos/{link}', [LinkCompartilhamentoController::class, 'destroy'])
        ->name('compartilhamentos.destroy');
});

require __DIR__.'/settings.php';
