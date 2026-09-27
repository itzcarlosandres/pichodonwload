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

    /**
     * Descarga una captura de pantalla remota, la optimiza y la guarda localmente como WebP
     * eliminando cualquier referencia o enlace a dominios externos como CDRomance.
     */
    public function downloadAndProcessScreenshot(string $remoteUrl): ?string
    {
        $remoteUrl = trim($remoteUrl);
        if (empty($remoteUrl) || !filter_var($remoteUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'ss_down_');

        try {
            $fp = fopen($tempPath, 'wb');
            $ch = curl_init($remoteUrl);
            curl_setopt_array($ch, [
                CURLOPT_FILE => $fp,
                CURLOPT_HEADER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ]);
            $success = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);

            if (!$success || $httpCode < 200 || $httpCode >= 400 || !file_exists($tempPath) || filesize($tempPath) < 500) {
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
                return null;
            }

            $fakeFile = new UploadedFile($tempPath, 'screenshot.jpg', 'image/jpeg', null, true);
            $processed = $this->processScreenshot($fakeFile);

            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }

            return $processed['url'] ?? null;
        } catch (Throwable $e) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
            return null;
        }
    }
}

