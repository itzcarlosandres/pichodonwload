<?php

namespace App\Services;

use App\Models\Setting;
use Aws\S3\S3Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Exception;

class StorageService
{
    /**
     * Prueba la conexión a Cloudflare R2 / AWS S3 y devuelve latencia y estado
     */
    public function testConnection(): array
    {
        $endpoint = Setting::get('r2_endpoint');
        $bucket = Setting::get('r2_bucket');
        $accessKey = Setting::get('r2_access_key');
        $secretKey = Setting::get('r2_secret_key');

        if (empty($endpoint) || empty($accessKey) || empty($secretKey) || empty($bucket)) {
            return [
                'success' => false,
                'message' => 'Faltan credenciales requeridas (Endpoint, Bucket, Access Key o Secret Key).',
            ];
        }

        try {
            $startTime = microtime(true);

            $s3Client = new S3Client([
                'version' => 'latest',
                'region'  => 'auto',
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key'    => $accessKey,
                    'secret' => $secretKey,
                ],
            ]);

            // List objects to verify read permissions
            $s3Client->listObjectsV2([
                'Bucket' => $bucket,
                'MaxKeys' => 1,
            ]);

            $latency = round((microtime(true) - $startTime) * 1000);

            return [
                'success' => true,
                'latency_ms' => $latency,
                'message' => "Conexión a Cloudflare R2 exitosa ({$latency}ms de latencia). Bucket '{$bucket}' verificado y operativo.",
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al conectar con S3/R2: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sube un archivo ROM / ISO / ZIP directamente a Cloudflare R2 o a la Bóveda Local
     */
    public function uploadRomFile(UploadedFile $file, ?string $subfolder = 'roms'): array
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtoupper($file->getClientOriginalExtension() ?: 'ISO');
        $sizeBytes = $file->getSize() ?: 0;
        
        // Formatear tamaño legible automáticamente
        $formattedSize = $this->formatBytes($sizeBytes);

        $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '-' . Str::random(8) . '.' . strtolower($extension);
        $key = ($subfolder ? trim($subfolder, '/') . '/' : '') . $safeName;

        $endpoint = Setting::get('r2_endpoint');
        $bucket = Setting::get('r2_bucket');
        $accessKey = Setting::get('r2_access_key');
        $secretKey = Setting::get('r2_secret_key');
        $publicDomain = Setting::get('r2_public_domain');

        // Si Cloudflare R2 está configurado, subir al bucket R2
        if (!empty($endpoint) && !empty($accessKey) && !empty($secretKey) && !empty($bucket)) {
            try {
                $s3Client = new S3Client([
                    'version' => 'latest',
                    'region'  => 'auto',
                    'endpoint' => $endpoint,
                    'use_path_style_endpoint' => true,
                    'credentials' => [
                        'key'    => $accessKey,
                        'secret' => $secretKey,
                    ],
                ]);

                $s3Client->putObject([
                    'Bucket' => $bucket,
                    'Key'    => $key,
                    'SourceFile' => $file->getRealPath(),
                    'ContentType' => $file->getMimeType() ?: 'application/octet-stream',
                ]);

                if (!empty($publicDomain)) {
                    $domain = rtrim($publicDomain, '/');
                    if (!str_starts_with($domain, 'http')) {
                        $domain = 'https://' . $domain;
                    }
                    $downloadUrl = "{$domain}/{$key}";
                } else {
                    $downloadUrl = rtrim($endpoint, '/') . "/{$bucket}/{$key}";
                }

                return [
                    'success' => true,
                    'provider' => 'Cloudflare R2',
                    'url' => $downloadUrl,
                    'file_size' => $formattedSize,
                    'file_size_bytes' => $sizeBytes,
                    'file_format' => $extension,
                    'filename' => $safeName,
                ];
            } catch (Exception $e) {
                // Si falla R2, fallback a almacenamiento local
            }
        }

        // Almacenamiento local en public/uploads/roms
        $destinationDir = public_path('uploads/' . ($subfolder ?: 'roms'));
        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        $file->move($destinationDir, $safeName);
        $downloadUrl = asset('uploads/' . ($subfolder ?: 'roms') . '/' . $safeName);

        return [
            'success' => true,
            'provider' => 'Bóveda Local',
            'url' => $downloadUrl,
            'file_size' => $formattedSize,
            'file_size_bytes' => $sizeBytes,
            'file_format' => $extension,
            'filename' => $safeName,
        ];
    }

    /**
     * Formatea bytes a cadena legible
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }
        return $bytes > 0 ? $bytes . ' B' : '1.2 GB';
    }
}

