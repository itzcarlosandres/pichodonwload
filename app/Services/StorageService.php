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
        $endpoint = Setting::get('r2_endpoint') ?: config('filesystems.disks.s3.endpoint');
        $bucket = Setting::get('r2_bucket') ?: config('filesystems.disks.s3.bucket');
        $accessKey = Setting::get('r2_access_key_id') ?: Setting::get('r2_access_key') ?: config('filesystems.disks.s3.key');
        $secretKey = Setting::get('r2_secret_access_key') ?: Setting::get('r2_secret_key') ?: config('filesystems.disks.s3.secret');

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
                'region'  => Setting::get('r2_region') ?: config('filesystems.disks.s3.region') ?: 'auto',
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => false,
                'http' => [
                    'verify' => config('filesystems.disks.s3.http.verify', true),
                ],
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
        $rawExt = strtolower($file->getClientOriginalExtension() ?: '');
        
        // Medida de seguridad: Bloquear extensiones ejecutables o scripts maliciosos
        $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'phar', 'cgi', 'pl', 'exe', 'sh', 'bash', 'bat', 'cmd', 'js', 'py'];
        if (in_array($rawExt, $dangerousExtensions, true)) {
            throw new Exception("El tipo de archivo '.{$rawExt}' está bloqueado por razones de seguridad.");
        }

        $extension = strtoupper($rawExt ?: 'ISO');
        $sizeBytes = $file->getSize() ?: 0;
        
        // Formatear tamaño legible automáticamente
        $formattedSize = $this->formatBytes($sizeBytes);

        $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '-' . Str::random(8) . '.' . strtolower($extension);
        $key = ($subfolder ? trim($subfolder, '/') . '/' : '') . $safeName;

        $endpoint = Setting::get('r2_endpoint') ?: config('filesystems.disks.s3.endpoint');
        $bucket = Setting::get('r2_bucket') ?: config('filesystems.disks.s3.bucket');
        $accessKey = Setting::get('r2_access_key_id') ?: Setting::get('r2_access_key') ?: config('filesystems.disks.s3.key');
        $secretKey = Setting::get('r2_secret_access_key') ?: Setting::get('r2_secret_key') ?: config('filesystems.disks.s3.secret');
        $publicDomain = Setting::get('r2_public_url') ?: Setting::get('r2_public_domain') ?: config('filesystems.disks.s3.url');

        // Si Cloudflare R2 está configurado, subir al bucket R2
        if (!empty($endpoint) && !empty($accessKey) && !empty($secretKey) && !empty($bucket)) {
            try {
                $s3Client = new S3Client([
                    'version' => 'latest',
                    'region'  => Setting::get('r2_region') ?: config('filesystems.disks.s3.region') ?: 'auto',
                    'endpoint' => $endpoint,
                    'use_path_style_endpoint' => false,
                    'http' => [
                        'verify' => config('filesystems.disks.s3.http.verify', true),
                    ],
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

    /**
     * Genera una URL prefirmada (PUT) para que el navegador suba directamente a Cloudflare R2
     * sin pasar por el servidor VPS ni chocar con el límite de 100 MB de Cloudflare.
     */
    public function getPresignedUploadUrl(string $originalFilename, int $sizeBytes, ?string $contentType = null, ?string $subfolder = 'roms'): array
    {
        $extension = strtoupper(pathinfo($originalFilename, PATHINFO_EXTENSION) ?: 'BIN');
        $formattedSize = $this->formatBytes($sizeBytes);
        $safeName = Str::slug(pathinfo($originalFilename, PATHINFO_FILENAME)) . '-' . Str::random(8) . '.' . strtolower($extension);
        $key = ($subfolder ? trim($subfolder, '/') . '/' : '') . $safeName;

        $endpoint = Setting::get('r2_endpoint') ?: config('filesystems.disks.s3.endpoint');
        $bucket = Setting::get('r2_bucket') ?: config('filesystems.disks.s3.bucket');
        $accessKey = Setting::get('r2_access_key_id') ?: Setting::get('r2_access_key') ?: config('filesystems.disks.s3.key');
        $secretKey = Setting::get('r2_secret_access_key') ?: Setting::get('r2_secret_key') ?: config('filesystems.disks.s3.secret');
        $publicDomain = Setting::get('r2_public_url') ?: Setting::get('r2_public_domain') ?: config('filesystems.disks.s3.url');

        if (empty($endpoint) || empty($accessKey) || empty($secretKey) || empty($bucket)) {
            return [
                'success' => false,
                'message' => 'Faltan credenciales de Cloudflare R2 para generar la subida directa.',
            ];
        }

        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region'  => Setting::get('r2_region') ?: config('filesystems.disks.s3.region') ?: 'auto',
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => false,
                'http' => [
                    'verify' => config('filesystems.disks.s3.http.verify', true),
                ],
                'credentials' => [
                    'key'    => $accessKey,
                    'secret' => $secretKey,
                ],
            ]);

            $cmd = $s3Client->getCommand('PutObject', [
                'Bucket' => $bucket,
                'Key' => $key,
                'ContentType' => $contentType ?: 'application/octet-stream',
            ]);

            // Válido por 3 horas para subidas lentas de archivos gigantescos (1 GB - 5 GB)
            $presignedRequest = $s3Client->createPresignedRequest($cmd, '+3 hours');
            $presignedUrl = (string) $presignedRequest->getUri();

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
                'upload_mode' => 'direct_r2',
                'presigned_url' => $presignedUrl,
                'download_url' => $downloadUrl,
                'key' => $key,
                'filename' => $safeName,
                'file_size' => $formattedSize,
                'file_size_bytes' => $sizeBytes,
                'file_format' => $extension,
                'content_type' => $contentType ?: 'application/octet-stream',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al generar URL prefirmada: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Asegura que el bucket tenga configurada la regla CORS para permitir PUT desde el navegador
     */
    public function ensureBucketCors(): array
    {
        $endpoint = Setting::get('r2_endpoint') ?: config('filesystems.disks.s3.endpoint');
        $bucket = Setting::get('r2_bucket') ?: config('filesystems.disks.s3.bucket');
        $accessKey = Setting::get('r2_access_key_id') ?: Setting::get('r2_access_key') ?: config('filesystems.disks.s3.key');
        $secretKey = Setting::get('r2_secret_access_key') ?: Setting::get('r2_secret_key') ?: config('filesystems.disks.s3.secret');

        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region'  => Setting::get('r2_region') ?: config('filesystems.disks.s3.region') ?: 'auto',
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => false,
                'http' => [
                    'verify' => config('filesystems.disks.s3.http.verify', true),
                ],
                'credentials' => [
                    'key'    => $accessKey,
                    'secret' => $secretKey,
                ],
            ]);

            $s3Client->putBucketCors([
                'Bucket' => $bucket,
                'CORSConfiguration' => [
                    'CORSRules' => [
                        [
                            'AllowedHeaders' => ['*'],
                            'AllowedMethods' => ['GET', 'PUT', 'HEAD'],
                            'AllowedOrigins' => ['*'],
                            'MaxAgeSeconds' => 3600,
                        ],
                    ],
                ],
            ]);

            return ['success' => true, 'message' => 'CORS configurado en R2 con éxito.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Comprueba si un objeto ya existe en la bóveda Cloudflare R2
     */
    public function objectExists(string $key): bool
    {
        $endpoint = Setting::get('r2_endpoint') ?: config('filesystems.disks.s3.endpoint');
        $bucket = Setting::get('r2_bucket') ?: config('filesystems.disks.s3.bucket');
        $accessKey = Setting::get('r2_access_key_id') ?: Setting::get('r2_access_key') ?: config('filesystems.disks.s3.key');
        $secretKey = Setting::get('r2_secret_access_key') ?: Setting::get('r2_secret_key') ?: config('filesystems.disks.s3.secret');

        if (empty($endpoint) || empty($accessKey) || empty($secretKey) || empty($bucket)) {
            return false;
        }

        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region'  => Setting::get('r2_region') ?: config('filesystems.disks.s3.region') ?: 'auto',
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => false,
                'http' => [
                    'verify' => config('filesystems.disks.s3.http.verify', true),
                ],
                'credentials' => [
                    'key'    => $accessKey,
                    'secret' => $secretKey,
                ],
            ]);

            return $s3Client->doesObjectExist($bucket, $key);
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Descarga un archivo desde una URL remota de forma eficiente (streaming en disco temporal)
     * y lo sube directamente al bucket de la Bóveda Privada Cloudflare R2.
     *
     * @param string $sourceUrl URL remota pública del archivo
     * @param string $destinationKey Ruta relativa en el bucket (ej. 'vault/bios/ps2-bios.zip')
     * @param string|null $contentType Tipo MIME opcional
     * @return array
     */
    public function uploadFromUrl(string $sourceUrl, string $destinationKey, ?string $contentType = null): array
    {
        $endpoint = Setting::get('r2_endpoint') ?: config('filesystems.disks.s3.endpoint');
        $bucket = Setting::get('r2_bucket') ?: config('filesystems.disks.s3.bucket');
        $accessKey = Setting::get('r2_access_key_id') ?: Setting::get('r2_access_key') ?: config('filesystems.disks.s3.key');
        $secretKey = Setting::get('r2_secret_access_key') ?: Setting::get('r2_secret_key') ?: config('filesystems.disks.s3.secret');
        $publicDomain = Setting::get('r2_public_url') ?: Setting::get('r2_public_domain') ?: config('filesystems.disks.s3.url');

        if (empty($endpoint) || empty($accessKey) || empty($secretKey) || empty($bucket)) {
            throw new Exception('Faltan credenciales configuradas para la Bóveda de Almacenamiento.');
        }

        // Descargar usando un archivo temporal en disco para soportar archivos de cualquier tamaño sin saturar RAM
        $tempDir = storage_path('app/temp_vault');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFilePath = $tempDir . '/' . uniqid('vault_download_', true) . '.tmp';

        // Codificar espacios en la URL para evitar errores de URI malformada
        $sourceUrl = str_replace(' ', '%20', $sourceUrl);

        $streamContext = stream_context_create([
            'http' => [
                'follow_location' => 1,
                'max_redirects' => 5,
                'timeout' => 300,
                'header' => "Referer: https://cdromance.org/bios-files/\r\nUser-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n",
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $remoteHandle = @fopen($sourceUrl, 'rb', false, $streamContext);

        if (!$remoteHandle) {
            throw new Exception("No se pudo conectar o descargar el archivo desde: {$sourceUrl}");
        }

        $localHandle = fopen($tempFilePath, 'wb');
        if (!$localHandle) {
            fclose($remoteHandle);
            throw new Exception("No se pudo crear el archivo temporal en el servidor local.");
        }

        // Copia en streaming
        $bytesCopied = stream_copy_to_stream($remoteHandle, $localHandle);
        fclose($remoteHandle);
        fclose($localHandle);

        if ($bytesCopied === false || $bytesCopied <= 0) {
            if (file_exists($tempFilePath)) {
                @unlink($tempFilePath);
            }
            throw new Exception("La descarga remota falló o el archivo descargado tiene 0 bytes.");
        }

        // Calcular hash MD5 e info del archivo
        $fileMd5 = md5_file($tempFilePath);
        $fileSha1 = sha1_file($tempFilePath);
        $formattedSize = $this->formatBytes($bytesCopied);

        try {
            $s3Client = new S3Client([
                'version' => 'latest',
                'region'  => Setting::get('r2_region') ?: config('filesystems.disks.s3.region') ?: 'auto',
                'endpoint' => $endpoint,
                'use_path_style_endpoint' => false,
                'http' => [
                    'verify' => config('filesystems.disks.s3.http.verify', true),
                ],
                'credentials' => [
                    'key'    => $accessKey,
                    'secret' => $secretKey,
                ],
            ]);

            $s3Client->putObject([
                'Bucket' => $bucket,
                'Key' => $destinationKey,
                'SourceFile' => $tempFilePath,
                'ContentType' => $contentType ?: 'application/zip',
            ]);

            if (!empty($publicDomain)) {
                $domain = rtrim($publicDomain, '/');
                if (!str_starts_with($domain, 'http')) {
                    $domain = 'https://' . $domain;
                }
                $downloadUrl = "{$domain}/{$destinationKey}";
            } else {
                $downloadUrl = rtrim($endpoint, '/') . "/{$bucket}/{$destinationKey}";
            }

            return [
                'success' => true,
                'url' => $downloadUrl,
                'size' => $formattedSize,
                'size_bytes' => $bytesCopied,
                'md5' => $fileMd5,
                'sha1' => $fileSha1,
                'key' => $destinationKey,
            ];
        } finally {
            if (file_exists($tempFilePath)) {
                @unlink($tempFilePath);
            }
        }
    }
}


