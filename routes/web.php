<?php

use App\Http\Controllers\BookmarkGrupoController;
use App\Http\Controllers\GrupoBookmarkController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::resource('grupos', GrupoBookmarkController::class)
        ->parameters(['grupos' => 'grupo'])
        ->only(['index', 'store', 'update', 'destroy']);

    Route::patch('bookmarks/{bookmark}/grupo', [BookmarkGrupoController::class, 'update'])
        ->name('bookmarks.grupo.update');
});

require __DIR__.'/settings.php';
