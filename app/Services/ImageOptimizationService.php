<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Throwable;

class ImageOptimizationService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new GdDriver());
    }

    /**
     * Optimiza y convierte una carátula HD a formato WebP (ratio 3:4.1) y genera thumbnail
     */
    public function processCover(UploadedFile $file): array
    {
        $filename = Str::random(24);
        $publicDir = public_path('uploads/covers');
        
        if (!file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        $fullPath = $publicDir . '/' . $filename . '.webp';
        $thumbPath = $publicDir . '/' . $filename . '_thumb.webp';

        try {
            // 1. Full HD WebP Cover (600x820 px)
            $image = $this->manager->decodePath($file->getRealPath());
            $image->cover(600, 820);
            $image->save($fullPath, quality: 85);

            // 2. Lightweight WebP Thumbnail (180x246 px)
            $thumb = $this->manager->decodePath($file->getRealPath());
            $thumb->cover(180, 246);
            $thumb->save($thumbPath, quality: 75);
        } catch (Throwable $e) {
            // Fallback: copy original if image processing fails
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $file->move($publicDir, $filename . '.' . $ext);
            $fullPath = $publicDir . '/' . $filename . '.' . $ext;
            $thumbPath = $fullPath;

            return [
                'url' => asset('uploads/covers/' . $filename . '.' . $ext),
                'thumb_url' => asset('uploads/covers/' . $filename . '.' . $ext),
                'path' => $fullPath,
                'thumb_path' => $thumbPath,
            ];
        }

        return [
            'url' => asset('uploads/covers/' . $filename . '.webp'),
            'thumb_url' => asset('uploads/covers/' . $filename . '_thumb.webp'),
            'path' => $fullPath,
            'thumb_path' => $thumbPath,
        ];
    }

    /**
     * Optimiza y convierte un banner panorámico 16:9 a WebP
     */
    public function processBanner(UploadedFile $file): array
    {
        $filename = Str::random(24);
        $publicDir = public_path('uploads/banners');
        
        if (!file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        $fullPath = $publicDir . '/' . $filename . '.webp';

        try {
            // 16:9 Banner (1600x900 px)
            $image = $this->manager->decodePath($file->getRealPath());
            $image->cover(1600, 900);
            $image->save($fullPath, quality: 85);
        } catch (Throwable $e) {
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $file->move($publicDir, $filename . '.' . $ext);
            return [
                'url' => asset('uploads/banners/' . $filename . '.' . $ext),
                'path' => $publicDir . '/' . $filename . '.' . $ext,
            ];
        }

        return [
            'url' => asset('uploads/banners/' . $filename . '.webp'),
            'path' => $fullPath,
        ];
    }

    /**
     * Procesa capturas de pantalla a WebP
     */
    public function processScreenshot(UploadedFile $file): array
    {
        $filename = Str::random(24);
        $publicDir = public_path('uploads/screenshots');
        
        if (!file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        $fullPath = $publicDir . '/' . $filename . '.webp';

        try {
            $image = $this->manager->decodePath($file->getRealPath());
            $image->scaleDown(1920, 1080);
            $image->save($fullPath, quality: 85);
        } catch (Throwable $e) {
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $file->move($publicDir, $filename . '.' . $ext);
            return [
                'url' => asset('uploads/screenshots/' . $filename . '.' . $ext),
                'path' => $publicDir . '/' . $filename . '.' . $ext,
            ];
        }

        return [
            'url' => asset('uploads/screenshots/' . $filename . '.webp'),
            'path' => $fullPath,
        ];
    }
}

