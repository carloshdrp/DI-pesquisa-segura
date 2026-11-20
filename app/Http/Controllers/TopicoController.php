<?php

namespace App\Http\Controllers;

use App\Services\TopicosPopularesService;
use Illuminate\Http\JsonResponse;

class TopicoController extends Controller
{
    /**
     * Tópicos mais pesquisados, calculados pelo comando agendado (RF12).
     */
    public function __invoke(TopicosPopularesService $topicos): JsonResponse
    {
        return response()->json(['data' => $topicos->obter()]);
    }
}
