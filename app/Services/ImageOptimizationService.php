<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Throwable;

class ImageOptimizationService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new GdDriver);
    }

    /**
     * Optimiza y convierte una carátula HD a formato WebP (ratio 3:4.1) y genera thumbnail
     */
    public function processCover(UploadedFile $file): array
    {
        $filename = Str::random(24);
        $publicDir = public_path('uploads/covers');

        if (! file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        $fullPath = $publicDir.'/'.$filename.'.webp';
        $thumbPath = $publicDir.'/'.$filename.'_thumb.webp';

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
            $file->move($publicDir, $filename.'.'.$ext);
            $fullPath = $publicDir.'/'.$filename.'.'.$ext;
            $thumbPath = $fullPath;

            return [
                'url' => asset('uploads/covers/'.$filename.'.'.$ext),
                'thumb_url' => asset('uploads/covers/'.$filename.'.'.$ext),
                'path' => $fullPath,
                'thumb_path' => $thumbPath,
            ];
        }

        return [
            'url' => asset('uploads/covers/'.$filename.'.webp'),
            'thumb_url' => asset('uploads/covers/'.$filename.'_thumb.webp'),
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

        if (! file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        $fullPath = $publicDir.'/'.$filename.'.webp';

        try {
            // 16:9 Banner (1600x900 px)
            $image = $this->manager->decodePath($file->getRealPath());
            $image->cover(1600, 900);
            $image->save($fullPath, quality: 85);
        } catch (Throwable $e) {
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $file->move($publicDir, $filename.'.'.$ext);

            return [
                'url' => asset('uploads/banners/'.$filename.'.'.$ext),
                'path' => $publicDir.'/'.$filename.'.'.$ext,
            ];
        }

        return [
            'url' => asset('uploads/banners/'.$filename.'.webp'),
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

        if (! file_exists($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        $fullPath = $publicDir.'/'.$filename.'.webp';

        try {
            $image = $this->manager->decodePath($file->getRealPath());
            $image->scaleDown(1920, 1080);
            $image->save($fullPath, quality: 85);
        } catch (Throwable $e) {
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $file->move($publicDir, $filename.'.'.$ext);

            return [
                'url' => asset('uploads/screenshots/'.$filename.'.'.$ext),
                'path' => $publicDir.'/'.$filename.'.'.$ext,
            ];
        }

        return [
            'url' => asset('uploads/screenshots/'.$filename.'.webp'),
            'path' => $fullPath,
        ];
    }

    /**
     * Descarga una captura de pantalla remota, la optimiza y la guarda localmente como WebP
     * o la sube opcionalmente a Cloudflare R2, eliminando cualquier referencia a CDRomance.
     */
    public function downloadAndProcessScreenshot(string $remoteUrl, bool $uploadToR2 = false): ?string
    {
        $remoteUrl = trim($remoteUrl);
        if (empty($remoteUrl) || ! filter_var($remoteUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'ss_down_');

        try {
            $fp = fopen($tempPath, 'wb');
            $ch = curl_init($remoteUrl);

            $host = parse_url($remoteUrl, PHP_URL_HOST) ?? 'cdromance.org';
            $referer = (parse_url($remoteUrl, PHP_URL_SCHEME) ?? 'https').'://'.$host.'/';

            curl_setopt_array($ch, [
                CURLOPT_FILE => $fp,
                CURLOPT_HEADER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 25,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_REFERER => $referer,
                CURLOPT_HTTPHEADER => [
                    'Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                    'Sec-Fetch-Dest: image',
                    'Sec-Fetch-Mode: no-cors',
                    'Sec-Fetch-Site: cross-site',
                ],
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            ]);
            $success = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);

            if (! $success || $httpCode < 200 || $httpCode >= 400 || ! file_exists($tempPath) || filesize($tempPath) < 500) {
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

            $finalUrl = $processed['url'] ?? null;
            $localFullPath = $processed['path'] ?? null;

            // Si se solicita subir exclusivamente a Cloudflare R2 y R2 está configurado
            if ($uploadToR2 && $localFullPath && file_exists($localFullPath)) {
                $storage = app(StorageService::class);
                if ($storage->isR2Configured()) {
                    $key = 'screenshots/'.basename($localFullPath);
                    $r2Url = $storage->uploadPathToR2($localFullPath, $key, 'image/webp');
                    if ($r2Url) {
                        // Eliminar copia local para ahorrar espacio en disco VPS
                        @unlink($localFullPath);
                        $finalUrl = $r2Url;
                    }
                }
            }

            return $finalUrl;
        } catch (Throwable $e) {
            Log::warning('Error procesando captura remota: '.$e->getMessage());
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }

            return null;
        }
    }
}
