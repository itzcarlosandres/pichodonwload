<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminBannerController extends Controller
{
    protected ImageOptimizationService $imageService;

    public function __construct(ImageOptimizationService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(): View
    {
        $banners = Banner::orderBy('order')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'banner_image' => 'nullable|image|max:20480',
            'image_url' => 'nullable|string|max:500',
            'target_url' => 'nullable|string|max:500',
            'badge_text' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        if ($request->hasFile('banner_image')) {
            $bannerData = $this->imageService->processBanner($request->file('banner_image'));
            $validated['image_url'] = $bannerData['url'];
        }

        if (empty($validated['image_url'])) {
            $validated['image_url'] = 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=1600&q=80';
        }

        Banner::create($validated);

        return redirect()->route('admin.banners.index')->with('success', 'Banner creado exitosamente.');
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'banner_image' => 'nullable|image|max:20480',
            'image_url' => 'nullable|string|max:500',
            'target_url' => 'nullable|string|max:500',
            'badge_text' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        if ($request->hasFile('banner_image')) {
            $bannerData = $this->imageService->processBanner($request->file('banner_image'));
            $validated['image_url'] = $bannerData['url'];
        }

        $banner->update($validated);

        return redirect()->route('admin.banners.index')->with('success', "Banner '{$banner->title}' actualizado.");
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $title = $banner->title;
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', "Banner '{$title}' eliminado.");
    }
}
