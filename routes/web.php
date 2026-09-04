<?php

use App\Jobs\CrossrefBuscaJob;
use App\Services\ArXivService;
use App\Services\OpenAlexService;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/teste', function () {
    CrossrefBuscaJob::dispatch('Machine Learning');

    return 'Foi!';
});
Route::get('/teste2', function () {
    $service = new OpenAlexService;

    return $service->buscar('Teste');
});
Route::get('/teste3', function () {
    $service = new ArXivService;

    return $service->buscar('Teste');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
