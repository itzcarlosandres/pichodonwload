<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AiContentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminAiController extends Controller
{
    protected AiContentService $aiService;

    public function __construct(AiContentService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function generateSeo(Request $request): JsonResponse
    {
        $title = $request->input('title', 'Videojuego');
        $console = $request->input('console', 'Consola');

        $result = $this->aiService->generateSeo($title, $console);

        return response()->json($result);
    }

    public function generateDescription(Request $request): JsonResponse
    {
        $title = $request->input('title', 'Videojuego');
        $console = $request->input('console', 'Consola');

        $result = $this->aiService->generateRichDescription($title, $console);

        return response()->json($result);
    }

    public function autocompleteSpecs(Request $request): JsonResponse
    {
        $title = $request->input('title', 'Videojuego');
        $console = $request->input('console', 'Consola');

        $result = $this->aiService->autocompleteSpecs($title, $console);

        return response()->json($result);
    }

    public function generateFranchise(Request $request): JsonResponse
    {
        $name = $request->input('name', 'Saga de Videojuegos');
        $result = $this->aiService->generateFranchiseData($name);

        return response()->json($result);
    }
}
