<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Console;
use App\Models\Category;
use App\Models\Badge;
use App\Models\Franchise;
use App\Services\ImageOptimizationService;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class AdminGameController extends Controller
{
    protected ImageOptimizationService $imageService;
    protected StorageService $storageService;

    public function __construct(ImageOptimizationService $imageService, StorageService $storageService)
    {
        $this->imageService = $imageService;
        $this->storageService = $storageService;
    }

    public function uploadRom(Request $request): JsonResponse
    {
        $request->validate([
            'rom_file' => 'required|file',
            'console_id' => 'nullable|exists:consoles,id',
        ]);

        $consoleSlug = $request->filled('console_id') ? Console::find($request->input('console_id'))?->slug : 'roms';
        $res = $this->storageService->uploadRomFile($request->file('rom_file'), "roms/{$consoleSlug}");

        return response()->json($res);
    }

    public function index(Request $request): View
    {
        $query = Game::with(['console', 'categories', 'badges']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('serial', 'like', "%{$search}%");
            });
        }

        if ($request->filled('console_id')) {
            $query->where('console_id', $request->input('console_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $games = $query->latest()->paginate(15)->withQueryString();
        $consoles = Console::orderBy('name')->get();

        return view('admin.games.index', compact('games', 'consoles'));
    }

    public function create(): View
    {
        $consoles = Console::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $badges = Badge::orderBy('name')->get();
        $franchises = Franchise::orderBy('name')->get();

        return view('admin.games.create', compact('consoles', 'categories', 'badges', 'franchises'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:games,slug',
            'console_id' => 'required|exists:consoles,id',
            'cover_image' => 'nullable|image|max:20480',
            'banner_image' => 'nullable|image|max:20480',
            'rom_file' => 'nullable|file',
            'description' => 'nullable|string',
            'release_year' => 'nullable|integer',
            'developer' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:50',
            'languages' => 'nullable|string|max:100',
            'file_size' => 'nullable|string|max:100',
            'file_size_bytes' => 'nullable|integer',
            'file_format' => 'nullable|string|max:20',
            'download_url' => 'nullable|string|max:2000',
            'mirror_url' => 'nullable|string|max:2000',
            'mirrors' => 'nullable|array',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'status' => 'required|in:PUBLISHED,DRAFT,ARCHIVED',
            'is_spotlight' => 'boolean',
            'is_featured' => 'boolean',
            'category_ids' => 'nullable|array',
            'badge_ids' => 'nullable|array',
        ]);

        $validated['is_spotlight'] = $request->boolean('is_spotlight');
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($validated['is_spotlight']) {
            Game::where('is_spotlight', true)->update(['is_spotlight' => false]);
        }

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Direct ROM File Upload to Cloudflare R2 / Local Vault
        if ($request->hasFile('rom_file')) {
            $consoleSlug = Console::find($validated['console_id'])?->slug ?: 'roms';
            $romData = $this->storageService->uploadRomFile($request->file('rom_file'), "roms/{$consoleSlug}");
            if (!empty($romData['url'])) {
                $validated['download_url'] = $romData['url'];
                if (empty($validated['file_size'])) {
                    $validated['file_size'] = $romData['file_size'];
                }
                if (empty($validated['file_format'])) {
                    $validated['file_format'] = $romData['file_format'];
                }
                $validated['file_size_bytes'] = $romData['file_size_bytes'];
            }
        }

        // Process dynamic mirror links
        if ($request->has('mirrors') && is_array($request->input('mirrors'))) {
            $cleanMirrors = [];
            foreach ($request->input('mirrors') as $m) {
                if (!empty($m['url'])) {
                    $cleanMirrors[] = [
                        'server' => !empty($m['server']) ? trim($m['server']) : 'Servidor Alternativo',
                        'url' => trim($m['url']),
                    ];
                }
            }
            $validated['download_links'] = $cleanMirrors;
        }

        // Process Cover Image to WebP
        if ($request->hasFile('cover_image')) {
            $coverData = $this->imageService->processCover($request->file('cover_image'));
            $validated['cover_url'] = $coverData['url'];
            $validated['cover_thumb_url'] = $coverData['thumb_url'];
        }

        // Process Banner Image to WebP
        if ($request->hasFile('banner_image')) {
            $bannerData = $this->imageService->processBanner($request->file('banner_image'));
            $validated['banner_url'] = $bannerData['url'];
        }

        $game = Game::create($validated);

        // Process Screenshot Files to WebP
        if ($request->hasFile('screenshot_files')) {
            $order = 1;
            foreach ($request->file('screenshot_files') as $file) {
                if ($file && $file->isValid()) {
                    $ssData = $this->imageService->processScreenshot($file);
                    $game->screenshots()->create([
                        'image_url' => $ssData['url'],
                        'image_webp_url' => $ssData['url'],
                        'order' => $order++,
                    ]);
                }
            }
        }

        if ($request->filled('category_ids')) {
            $game->categories()->sync($request->input('category_ids'));
        }

        if ($request->filled('badge_ids')) {
            $game->badges()->sync($request->input('badge_ids'));
        }

        if ($request->filled('franchise_ids')) {
            $game->franchises()->sync($request->input('franchise_ids'));
        }

        return redirect()->route('admin.games.index')->with('success', "El videojuego '{$game->title}' se ha creado y publicado exitosamente.");
    }

    public function edit(Game $game): View
    {
        $consoles = Console::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $badges = Badge::orderBy('name')->get();
        $franchises = Franchise::orderBy('name')->get();
        $game->load('screenshots');

        return view('admin.games.edit', compact('game', 'consoles', 'categories', 'badges', 'franchises'));
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:games,slug,' . $game->id,
            'console_id' => 'required|exists:consoles,id',
            'cover_image' => 'nullable|image|max:20480',
            'banner_image' => 'nullable|image|max:20480',
            'screenshot_files' => 'nullable|array',
            'screenshot_files.*' => 'nullable|image|max:20480',
            'delete_screenshot_ids' => 'nullable|array',
            'rom_file' => 'nullable|file',
            'description' => 'nullable|string',
            'release_year' => 'nullable|integer',
            'developer' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:50',
            'languages' => 'nullable|string|max:100',
            'file_size' => 'nullable|string|max:100',
            'file_size_bytes' => 'nullable|integer',
            'file_format' => 'nullable|string|max:20',
            'download_url' => 'nullable|string|max:2000',
            'mirror_url' => 'nullable|string|max:2000',
            'mirrors' => 'nullable|array',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'status' => 'required|in:PUBLISHED,DRAFT,ARCHIVED',
            'is_spotlight' => 'boolean',
            'is_featured' => 'boolean',
            'category_ids' => 'nullable|array',
            'badge_ids' => 'nullable|array',
            'franchise_ids' => 'nullable|array',
        ]);

        $validated['is_spotlight'] = $request->boolean('is_spotlight');
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($validated['is_spotlight']) {
            Game::where('id', '!=', $game->id)->where('is_spotlight', true)->update(['is_spotlight' => false]);
        }

        // Direct ROM File Upload to Cloudflare R2 / Local Vault
        if ($request->hasFile('rom_file')) {
            $consoleSlug = Console::find($validated['console_id'])?->slug ?: 'roms';
            $romData = $this->storageService->uploadRomFile($request->file('rom_file'), "roms/{$consoleSlug}");
            if (!empty($romData['url'])) {
                $validated['download_url'] = $romData['url'];
                if (empty($validated['file_size'])) {
                    $validated['file_size'] = $romData['file_size'];
                }
                if (empty($validated['file_format'])) {
                    $validated['file_format'] = $romData['file_format'];
                }
                $validated['file_size_bytes'] = $romData['file_size_bytes'];
            }
        }

        // Process dynamic mirror links
        if ($request->has('mirrors') && is_array($request->input('mirrors'))) {
            $cleanMirrors = [];
            foreach ($request->input('mirrors') as $m) {
                if (!empty($m['url'])) {
                    $cleanMirrors[] = [
                        'server' => !empty($m['server']) ? trim($m['server']) : 'Servidor Alternativo',
                        'url' => trim($m['url']),
                    ];
                }
            }
            $validated['download_links'] = $cleanMirrors;
        } else {
            $validated['download_links'] = [];
        }

        if ($request->hasFile('cover_image')) {
            $coverData = $this->imageService->processCover($request->file('cover_image'));
            $validated['cover_url'] = $coverData['url'];
            $validated['cover_thumb_url'] = $coverData['thumb_url'];
        }

        if ($request->hasFile('banner_image')) {
            $bannerData = $this->imageService->processBanner($request->file('banner_image'));
            $validated['banner_url'] = $bannerData['url'];
        }

        $game->update($validated);

        // Process deleted screenshots
        if ($request->filled('delete_screenshot_ids')) {
            $deleteIds = (array) $request->input('delete_screenshot_ids');
            $game->screenshots()->whereIn('id', $deleteIds)->delete();
        }

        // Process new Screenshot Files to WebP
        if ($request->hasFile('screenshot_files')) {
            $maxOrder = (int) ($game->screenshots()->max('order') ?? 0);
            foreach ($request->file('screenshot_files') as $file) {
                if ($file && $file->isValid()) {
                    $maxOrder++;
                    $ssData = $this->imageService->processScreenshot($file);
                    $game->screenshots()->create([
                        'image_url' => $ssData['url'],
                        'image_webp_url' => $ssData['url'],
                        'order' => $maxOrder,
                    ]);
                }
            }
        }

        if ($request->has('category_ids')) {
            $game->categories()->sync($request->input('category_ids', []));
        }

        if ($request->has('badge_ids')) {
            $game->badges()->sync($request->input('badge_ids', []));
        }

        if ($request->has('franchise_ids')) {
            $game->franchises()->sync($request->input('franchise_ids', []));
        }

        return redirect()->route('admin.games.index')->with('success', "Videojuego '{$game->title}' actualizado con éxito.");
    }

    public function destroy(Game $game): RedirectResponse
    {
        $title = $game->title;
        $game->delete();

        return redirect()->route('admin.games.index')->with('success', "Juego '{$title}' eliminado del catálogo.");
    }

    public function duplicate(Game $game): RedirectResponse
    {
        $newGame = $game->replicate();
        $newGame->title = $game->title . ' (Copia)';
        $newGame->slug = Str::slug($newGame->title . '-' . Str::random(5));
        $newGame->status = 'DRAFT';
        $newGame->save();

        $newGame->categories()->sync($game->categories->pluck('id'));
        $newGame->badges()->sync($game->badges->pluck('id'));

        return redirect()->route('admin.games.index')->with('success', "Juego duplicado como borrador: {$newGame->title}");
    }
}
