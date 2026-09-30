<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ScraperSafetyService
{
    /**
     * Tiempos mínimos de espera entre peticiones consecutivas por dominio (milisegundos)
     */
    protected int $minDelayMs = 1200;

    /**
     * Duración por defecto de la pausa de seguridad al detectar 429 o 403 (segundos)
     */
    protected int $cooldownSeconds = 60;

    /**
     * Agentes de usuario de navegadores reales actualizados (Chrome y Edge en Windows/Mac)
     */
    protected array $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Edg/124.0.0.0',
    ];

    /**
     * Normaliza el nombre del proveedor
     */
    public function normalizeProvider(string $providerOrUrl): string
    {
        $target = strtolower($providerOrUrl);
        if (str_contains($target, 'cdromance')) return 'cdromance';
        if (str_contains($target, 'romspedia')) return 'romspedia';
        if (str_contains($target, 'romsemu')) return 'romsemu';
        return 'general';
    }

    /**
     * Comprueba si hay una pausa de seguridad activa para el proveedor
     */
    public function checkCooldown(string $provider): ?array
    {
        $provider = $this->normalizeProvider($provider);
        $cooldownUntil = Cache::get("scraper_cooldown_{$provider}");

        if ($cooldownUntil && $cooldownUntil > time()) {
            $remaining = (int) ($cooldownUntil - time());
            $reason = Cache::get("scraper_cooldown_reason_{$provider}", 'Límite de peticiones preventivo');
            return [
                'active' => true,
                'provider' => $provider,
                'remaining_seconds' => $remaining,
                'reason' => $reason,
                'message' => "Pausa de seguridad activa para {$provider}: protegiendo tu IP por {$remaining}s más ({$reason}).",
            ];
        }

        return null;
    }

    /**
     * Activa una pausa de seguridad preventiva (Circuit Breaker)
     */
    public function triggerCooldown(string $provider, int $seconds = 60, string $reason = 'Límite de peticiones detectado (HTTP 429/403)'): void
    {
        $provider = $this->normalizeProvider($provider);
        $until = time() + $seconds;
        Cache::put("scraper_cooldown_{$provider}", $until, $seconds + 10);
        Cache::put("scraper_cooldown_reason_{$provider}", $reason, $seconds + 10);

        Log::warning("[ScraperSafety] Cooldown activado para {$provider} por {$seconds} segundos. Motivo: {$reason}");
    }

    /**
     * Reinicia manualmente la pausa de seguridad
     */
    public function resetCooldown(string $provider): void
    {
        $provider = $this->normalizeProvider($provider);
        Cache::forget("scraper_cooldown_{$provider}");
        Cache::forget("scraper_cooldown_reason_{$provider}");
        Cache::forget("scraper_last_request_{$provider}");
    }

    /**
     * Aplica una micro-pausa cortés con jitter para no saturar el servidor objetivo
     */
    public function applyPoliteThrottle(string $provider): void
    {
        $provider = $this->normalizeProvider($provider);
        $lastRequestTime = (float) Cache::get("scraper_last_request_{$provider}", 0);
        $now = microtime(true) * 1000; // Milisegundos actuales

        if ($lastRequestTime > 0) {
            $elapsed = $now - $lastRequestTime;
            if ($elapsed < $this->minDelayMs) {
                $sleepMs = ($this->minDelayMs - $elapsed) + random_int(100, 350);
                usleep((int) ($sleepMs * 1000));
            }
        }

        // Actualizar marca de tiempo
        Cache::put("scraper_last_request_{$provider}", microtime(true) * 1000, 120);
    }

    /**
     * Genera cabeceras realistas de navegador para simular tráfico humano auténtico
     */
    public function getRandomHeaders(string $url): array
    {
        $host = parse_url($url, PHP_URL_HOST) ?? 'www.google.com';
        $referer = "https://{$host}/";

        return [
            'User-Agent: ' . $this->userAgents[array_rand($this->userAgents)],
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language: es-ES,es;q=0.9,en-US;q=0.8,en;q=0.7',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1',
            'Sec-Ch-Ua: "Chromium";v="124", "Google Chrome";v="124", "Not-A.Brand";v="99"',
            'Sec-Ch-Ua-Mobile: ?0',
            'Sec-Ch-Ua-Platform: "Windows"',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: same-origin',
            'Sec-Fetch-User: ?1',
            "Referer: {$referer}",
        ];
    }

    /**
     * Ejecuta una petición HTTP segura con Throttle, Circuit Breaker y Caché preventiva
     */
    public function safeFetch(string $url, string $provider, array $customCurlOptions = [], bool $useCache = true, int $cacheSeconds = 600): array
    {
        $provider = $this->normalizeProvider($provider);

        // 1. Verificar si hay un Stop / Cooldown de seguridad activo
        $cooldown = $this->checkCooldown($provider);
        if ($cooldown) {
            return [
                'success' => false,
                'cooldown' => true,
                'http_code' => 429,
                'message' => $cooldown['message'],
                'remaining_seconds' => $cooldown['remaining_seconds'],
                'html' => null,
            ];
        }

        // 2. Caché de corto plazo para evitar peticiones repetitivas idénticas
        $cacheKey = 'scraper_html_' . md5($url);
        if ($useCache) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return [
                    'success' => true,
                    'cached' => true,
                    'http_code' => 200,
                    'html' => $cached,
                    'message' => 'Obtenido de la memoria caché segura.',
                ];
            }
        }

        // 3. Aplicar pausa cortés para no saturar
        $this->applyPoliteThrottle($provider);

        // 4. Ejecutar cURL
        $ch = curl_init($url);
        $headers = $this->getRandomHeaders($url);

        $defaultOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 18,
            CURLOPT_ENCODING => '', // Soporta gzip y deflate automáticamente
            CURLOPT_HTTPHEADER => $headers,
        ];

        curl_setopt_array($ch, $customCurlOptions + $defaultOptions);
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // 5. Detectar bloqueos o límites (HTTP 429 / 403 / Cloudflare)
        if ($httpCode === 429 || $httpCode === 403) {
            $this->triggerCooldown($provider, $this->cooldownSeconds, "El servidor devolvió HTTP {$httpCode}");
            return [
                'success' => false,
                'cooldown' => true,
                'http_code' => $httpCode,
                'message' => "Se ha activado una pausa de seguridad de {$this->cooldownSeconds}s para {$provider} porque devolvió HTTP {$httpCode} (protección anti-baneo).",
                'html' => null,
            ];
        }

        if ($httpCode >= 200 && $httpCode < 400 && !empty($html)) {
            // Guardar en caché preventiva
            if ($useCache) {
                Cache::put($cacheKey, $html, $cacheSeconds);
            }

            return [
                'success' => true,
                'cached' => false,
                'http_code' => $httpCode,
                'html' => $html,
                'message' => 'Petición exitosa.',
            ];
        }

        return [
            'success' => false,
            'cooldown' => false,
            'http_code' => $httpCode,
            'message' => $curlError ?: "El servidor respondió con código HTTP {$httpCode}.",
            'html' => null,
        ];
    }
}
