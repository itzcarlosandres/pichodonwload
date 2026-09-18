<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\StorageService;
use App\Services\AiContentService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class AdminSettingController extends Controller
{
    protected StorageService $storageService;
    protected AiContentService $aiService;

    public function __construct(StorageService $storageService, AiContentService $aiService)
    {
        $this->storageService = $storageService;
        $this->aiService = $aiService;
    }

    public function index(): View
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->except(['_token', '_method', 'site_logo_file', 'site_favicon_file']);

        // Handle custom logo image file upload if uploaded
        if ($request->hasFile('site_logo_file')) {
            $logoFile = $request->file('site_logo_file');
            $uploadResult = $this->storageService->uploadRomFile($logoFile, 'branding');
            if (!empty($uploadResult['success']) && !empty($uploadResult['url'])) {
                $data['site_logo_url'] = $uploadResult['url'];
            }
        }

        // Handle custom favicon image file upload if uploaded
        if ($request->hasFile('site_favicon_file')) {
            $favFile = $request->file('site_favicon_file');
            $uploadResult = $this->storageService->uploadRomFile($favFile, 'branding');
            if (!empty($uploadResult['success']) && !empty($uploadResult['url'])) {
                $data['site_favicon_url'] = $uploadResult['url'];
            }
        }

        foreach ($data as $key => $value) {
            $group = 'general';
            if (str_starts_with($key, 'r2_') || str_starts_with($key, 'storage_')) {
                $group = 'storage';
            } elseif (str_starts_with($key, 'seo_')) {
                $group = 'seo';
            } elseif (str_starts_with($key, 'ai_')) {
                $group = 'ai';
            }

            Setting::set($key, $value ?? '', $group);
        }

        \Illuminate\Support\Facades\Cache::flush();
        \Illuminate\Support\Facades\Artisan::call('view:clear');

        return redirect()->route('admin.settings.index')->with('success', 'Configuraciones del sistema e identidad visual guardadas exitosamente.');
    }

    public function testStorage(): JsonResponse
    {
        $result = $this->storageService->testConnection();
        return response()->json($result);
    }

    public function testGemini(Request $request): JsonResponse
    {
        $apiKey = $request->input('ai_api_key');
        $model = $request->input('ai_model');

        $result = $this->aiService->testConnection($apiKey, $model);
        return response()->json($result);
    }
}
