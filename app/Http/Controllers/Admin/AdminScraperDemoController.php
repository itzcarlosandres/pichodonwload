<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Console;
use App\Models\Category;
use App\Services\RomScraperService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

use App\Services\ImageOptimizationService;
use Illuminate\Http\UploadedFile;

use App\Services\RomCatalogBrowserService;
use App\Services\ScraperSafetyService;
use App\Services\AiContentService;

class AdminScraperDemoController extends Controller
{
    protected RomScraperService $scraperService;
    protected ImageOptimizationService $imageService;
    protected RomCatalogBrowserService $browserService;
    protected ScraperSafetyService $safetyService;
    protected AiContentService $aiService;

    public function __construct(
        RomScraperService $scraperService, 
        ImageOptimizationService $imageService,
        RomCatalogBrowserService $browserService,
        ScraperSafetyService $safetyService,
        AiContentService $aiService
    ) {
        $this->scraperService = $scraperService;
        $this->imageService = $imageService;
        $this->browserService = $browserService;
        $this->safetyService = $safetyService;
        $this->aiService = $aiService;
    }

    /**
     * Muestra la vista de demostración del extractor
     */
    public function index(): View
    {
        $consoles = Console::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        return view('admin.scraper-demo', compact('consoles', 'categories'));
    }

    /**
     * Endpoint AJAX para extraer datos de una URL de Romspedia
     */
    public function extract(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
        ], [
            'url.required' => 'Debes ingresar una URL.',
            'url.url' => 'El formato de la URL no es válido.',
        ]);

        $result = $this->scraperService->scrape($request->input('url'));

        if (!$result['success']) {
            $code = !empty($result['cooldown']) ? 429 : 422;
            return response()->json($result, $code);
        }

        return response()->json($result);
    }

    /**
     * Guarda el juego extraído directamente en la base de datos
     */
    public function saveToCatalog(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'cover_url' => 'nullable|string',
            'download_url' => 'required|string',
            'console_id' => 'required|exists:consoles,id',
            'file_size' => 'nullable|string',
            'file_format' => 'nullable|string',
            'status' => 'nullable|in:PUBLISHED,DRAFT',
            'optimize_cover' => 'nullable|boolean',
        ]);

        $title = $request->input('title');
        $slug = Str::slug($title);

        // Asegurar slug único
        $count = Game::where('slug', 'LIKE', "{$slug}%")->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $coverUrl = $request->input('cover_url');
        $thumbUrl = null;

        // Si se solicita descargar y optimizar la portada localmente a WebP
        if ($request->boolean('optimize_cover') && !empty($coverUrl)) {
            try {
                $tempPath = tempnam(sys_get_temp_dir(), 'cover_');
                $ch = curl_init($coverUrl);
                $fp = fopen($tempPath, 'wb');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);

                if (file_exists($tempPath) && filesize($tempPath) > 500) {
                    $uploadedFile = new UploadedFile($tempPath, 'cover.webp', 'image/webp', null, true);
                    $processed = $this->imageService->processCover($uploadedFile);
                    $coverUrl = $processed['url'];
                    $thumbUrl = $processed['thumb_url'];
                }
            } catch (\Throwable $e) {
                // Si falla la descarga local, mantener la URL remota
            }
        }

        $status = $request->input('status', 'DRAFT');

        $releaseYear = $request->filled('release_year') ? (int)$request->input('release_year') : null;
        $region = $request->input('region', 'USA');
        $languages = $request->input('languages', 'English');
        $publisher = $request->input('publisher');

        $game = Game::create([
            'title' => $title,
            'slug' => $slug,
            'console_id' => $request->input('console_id'),
            'cover_url' => $coverUrl,
            'cover_thumb_url' => $thumbUrl,
            'download_url' => $request->input('download_url'),
            'file_size' => $request->input('file_size') ?: '1.0 GB',
            'file_format' => $request->input('file_format') ?: 'ZIP',
            'release_year' => $releaseYear,
            'region' => $region,
            'languages' => $languages,
            'publisher' => $publisher,
            'status' => $status,
            'description' => 'Pendiente de generar con IA.',
            'download_count' => 0,
            'views_count' => 0,
        ]);

        if ($request->filled('category_ids') && is_array($request->input('category_ids'))) {
            $game->categories()->sync($request->input('category_ids'));
        }

        if ($request->filled('screenshots') && is_array($request->input('screenshots'))) {
            foreach ($request->input('screenshots') as $idx => $sUrl) {
                if (!empty($sUrl)) {
                    $game->screenshots()->create([
                        'image_url' => $sUrl,
                        'image_webp_url' => $sUrl,
                        'order' => $idx + 1,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => '¡Juego guardado exitosamente en el catálogo con categorías y capturas!',
            'game_id' => $game->id,
            'edit_url' => route('admin.games.edit', $game),
        ]);
    }

    /**
     * Muestra la vista del Explorador de Catálogo con importación en 1 clic
     */
    public function catalog(): View
    {
        $consoles = Console::orderBy('name')->get();
        return view('admin.scraper-catalog', compact('consoles'));
    }

    /**
     * Endpoint AJAX para consultar juegos de una página del catálogo
     */
    public function fetchCatalog(Request $request): JsonResponse
    {
        $provider = $request->input('provider', 'romspedia');
        $console = $request->input('console', 'psp');
        $page = max(1, (int) $request->input('page', 1));

        $result = $this->browserService->browse($provider, $console, $page);
        $status = !empty($result['cooldown']) ? 429 : ($result['success'] ? 200 : 422);

        return response()->json($result, $status);
    }

    /**
     * Endpoint AJAX para importar un juego en 1 solo clic
     */
    public function quickImport(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url',
            'status' => 'nullable|in:DRAFT,PUBLISHED',
            'optimize_cover' => 'nullable|boolean',
        ]);

        $url = $request->input('url');
        $scrape = $this->scraperService->scrape($url);

        if (!$scrape['success']) {
            $status = !empty($scrape['cooldown']) ? 429 : 422;
            return response()->json([
                'success' => false,
                'cooldown' => $scrape['cooldown'] ?? false,
                'remaining_seconds' => $scrape['remaining_seconds'] ?? null,
                'message' => $scrape['message'] ?? 'No se pudo extraer la información del juego.',
            ], $status);
        }

        $title = $scrape['title'];
        $slug = Str::slug($title);

        // Detectar si ya existe en la base de datos
        $existing = Game::where('slug', $slug)->orWhere('title', $title)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'already_existed' => true,
                'message' => "El juego '{$existing->title}' ya está en tu catálogo.",
                'game_id' => $existing->id,
                'edit_url' => route('admin.games.edit', $existing),
            ]);
        }

        // Buscar consola adecuada
        $console = null;
        if (!empty($scrape['platform_slug'])) {
            $console = Console::where('slug', $scrape['platform_slug'])->first();
        }
        if (!$console) {
            $console = Console::first();
        }

        // Procesar carátula
        $coverUrl = $scrape['cover_url'];
        $thumbUrl = null;

        if ($request->boolean('optimize_cover', true) && !empty($coverUrl)) {
            try {
                $tempPath = tempnam(sys_get_temp_dir(), 'quick_cov_');
                $ch = curl_init($coverUrl);
                $fp = fopen($tempPath, 'wb');
                curl_setopt_array($ch, [
                    CURLOPT_FILE => $fp,
                    CURLOPT_HEADER => false,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 15,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                ]);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);

                if (file_exists($tempPath) && filesize($tempPath) > 500) {
                    $uploadedFile = new UploadedFile($tempPath, 'cover.webp', 'image/webp', null, true);
                    $processed = $this->imageService->processCover($uploadedFile);
                    $coverUrl = $processed['url'];
                    $thumbUrl = $processed['thumb_url'];
                }
            } catch (\Throwable $e) {
                // Si falla la descarga local, mantener remota
            }
        }

        $status = $request->input('status', 'DRAFT');

        // Piloto Automático IA: Redactar Sinopsis (~200 palabras) y Meta SEO enriquecido si está activo
        $description = 'Importado automáticamente desde ' . ($scrape['source_provider'] ?? 'web') . '.';
        $metaTitle = null;
        $metaDescription = null;

        if ($request->boolean('generate_ai', true)) {
            try {
                $context = [
                    'release_year' => $scrape['release_year'] ?? null,
                    'region' => $scrape['region'] ?? null,
                    'languages' => $scrape['languages'] ?? null,
                    'genre' => $scrape['raw_genre'] ?? null,
                    'publisher' => $scrape['publisher'] ?? null,
                    'developer' => $scrape['developer'] ?? null,
                ];

                $descRes = $this->aiService->generateRichDescription($title, $console->name, $context);
                if (!empty($descRes['description'])) {
                    $description = $descRes['description'];
                }

                $seoRes = $this->aiService->generateSeo($title, $console->name, $context);
                if (!empty($seoRes['meta_title'])) {
                    $metaTitle = $seoRes['meta_title'];
                    $metaDescription = $seoRes['meta_description'];
                }
            } catch (\Throwable $e) {
                // Fallback sin interrumpir la importación
            }
        }

        $game = Game::create([
            'title' => $title,
            'slug' => $slug,
            'console_id' => $console->id,
            'cover_url' => $coverUrl,
            'cover_thumb_url' => $thumbUrl,
            'download_url' => $scrape['direct_download_url'] ?: $url,
            'file_size' => $scrape['file_size'] ?: '1.0 GB',
            'file_format' => $scrape['file_format'] ?: 'ZIP',
            'release_year' => $scrape['release_year'] ?? null,
            'region' => $scrape['region'] ?? 'USA',
            'languages' => $scrape['languages'] ?? 'English',
            'publisher' => $scrape['publisher'] ?? null,
            'status' => $status,
            'description' => $description,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'download_count' => 0,
            'views_count' => 0,
        ]);

        if (!empty($scrape['category_ids']) && is_array($scrape['category_ids'])) {
            $game->categories()->sync($scrape['category_ids']);
        }

        if (!empty($scrape['screenshots']) && is_array($scrape['screenshots'])) {
            foreach ($scrape['screenshots'] as $idx => $sUrl) {
                if (!empty($sUrl)) {
                    $game->screenshots()->create([
                        'image_url' => $sUrl,
                        'image_webp_url' => $sUrl,
                        'order' => $idx + 1,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "¡'{$game->title}' importado con éxito!",
            'game_id' => $game->id,
            'edit_url' => route('admin.games.edit', $game),
            'view_url' => route('game.show', $game->slug),
        ]);
    }

    /**
     * Endpoint AJAX para consultar pausas de seguridad (cooldown)
     */
    public function safetyStatus(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'romspedia_cooldown' => $this->safetyService->checkCooldown('romspedia'),
            'cdromance_cooldown' => $this->safetyService->checkCooldown('cdromance'),
        ]);
    }

    /**
     * Endpoint AJAX para restablecer pausas de seguridad manualmente
     */
    public function resetSafetyCooldown(Request $request): JsonResponse
    {
        $provider = $request->input('provider', 'all');
        if ($provider === 'all') {
            $this->safetyService->resetCooldown('romspedia');
            $this->safetyService->resetCooldown('cdromance');
        } else {
            $this->safetyService->resetCooldown($provider);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pausa de seguridad restablecida correctamente.',
        ]);
    }
}
