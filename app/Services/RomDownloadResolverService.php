<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RomDownloadResolverService
{
    /**
     * Resuelve un enlace de descarga en tiempo real.
     * Si es un enlace de CDRomance con token temporal (key), consulta la clave activa del día y la devuelve.
     * Si es un enlace estático (Romspedia, R2, Mega, etc.), lo devuelve intacto sin demora.
     */
    public function resolve(string $url): string
    {
        $url = trim($url);

        // Si no es un enlace dinámico de CDRomance, devolver directo
        if (!str_contains($url, 'cdromance.org') || !str_contains($url, 'download.php')) {
            return $url;
        }

        try {
            $query = parse_url($url, PHP_URL_QUERY);
            if (!$query) {
                return $url;
            }

            parse_str($query, $params);
            $postId = $params['id'] ?? null;
            $targetFile = $params['file'] ?? null;

            if (!$postId) {
                return $url;
            }

            // Clave de caché por ID de juego y archivo
            // CDRomance rota los tokens cada 24 horas (medianoche GMT); cachear por 6 horas es ideal
            $cacheKey = "cdr_dl_fresh_{$postId}_" . md5($targetFile ?? '') . '_' . date('YmdH');
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }

            // Consultar endpoint AJAX interno de CDRomance para obtener el token fresco del día
            $ajaxUrl = 'https://cdromance.org/wp-content/plugins/cdr-main/public/ajax.php';
            $ch = curl_init($ajaxUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query(['post_id' => $postId]),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 8,
                CURLOPT_HTTPHEADER => [
                    'X-Requested-With: XMLHttpRequest',
                    'Origin: https://cdromance.org',
                    'Accept: */*',
                ],
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (!$response || $httpCode >= 400) {
                return $url;
            }

            // Extraer enlaces con download.php devueltos en el HTML
            preg_match_all('/<a[^>]+href="([^">]+download\.php[^">]+)"/i', $response, $matches);
            if (empty($matches[1])) {
                return $url;
            }

            $selectedUrl = null;

            // Si hay un archivo específico buscado (ej: Disc 1, Hack, etc.)
            if ($targetFile) {
                $targetFileDecoded = urldecode($targetFile);
                foreach ($matches[1] as $cand) {
                    $candDecoded = html_entity_decode($cand);
                    if (str_contains($candDecoded, urlencode($targetFile)) || str_contains($candDecoded, $targetFileDecoded)) {
                        $selectedUrl = $candDecoded;
                        break;
                    }
                }
            }

            if (!$selectedUrl) {
                $selectedUrl = html_entity_decode($matches[1][0]);
            }

            // Guardar en caché por 6 horas para velocidad instantánea
            Cache::put($cacheKey, $selectedUrl, now()->addHours(6));

            return $selectedUrl;

        } catch (\Throwable $e) {
            Log::warning('Error en RomDownloadResolverService: ' . $e->getMessage());
            return $url;
        }
    }
}
