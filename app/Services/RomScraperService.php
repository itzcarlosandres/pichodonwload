<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Str;

class RomScraperService
{
    protected ScraperSafetyService $safety;

    public function __construct(?ScraperSafetyService $safety = null)
    {
        $this->safety = $safety ?? app(ScraperSafetyService::class);
    }

    /**
     * Extrae información esencial y metadatos desde Romspedia o CDRomance
     */
    public function scrape(string $url): array
    {
        $url = trim($url);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'success' => false,
                'message' => 'Por favor introduce una URL válida.',
            ];
        }

        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        if (str_contains($host, 'romspedia.com')) {
            return $this->scrapeRomspedia($url);
        } elseif (str_contains($host, 'cdromance.org')) {
            return $this->scrapeCdromance($url);
        }

        return [
            'success' => false,
            'message' => 'El dominio no está soportado. Actualmente puedes usar URLs de romspedia.com o cdromance.org',
        ];
    }

    /**
     * Scraper especializado para Romspedia.com
     */
    protected function scrapeRomspedia(string $url): array
    {
        $startTime = microtime(true);

        $res = $this->safety->safeFetch($url, 'romspedia');
        if (!$res['success']) {
            return [
                'success' => false,
                'cooldown' => $res['cooldown'] ?? false,
                'remaining_seconds' => $res['remaining_seconds'] ?? null,
                'message' => $res['message'] ?? 'No se pudo conectar o descargar la información desde Romspedia.',
            ];
        }

        $html = $res['html'];

        // Título
        $title = '';
        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $m)) {
            $title = trim(strip_tags($m[1]));
            $title = preg_replace('/\s+ROM Download.*$/i', '', $title);
        }

        // Portada ultra-robusta (contenedor img, static.romspedia, og:image)
        $coverUrl = '';
        if (preg_match('/<div[^>]*class=["\'][^"\']*(?:emulator-detail-img|view-emulator-detail-img|game-img|roms-img)[^"\']*["\'][^>]*>.*?<img[^>]+(?:data-src|srcset|src)=["\']([^"\']+)["\']/is', $html, $m)) {
            $coverUrl = trim($m[1]);
        } elseif (preg_match('/<img[^>]+(?:data-src|src)=["\']([^"\']*(?:\/roms\/|static\.romspedia\.com)[^"\']+\.(?:webp|png|jpg|jpeg))["\']/i', $html, $m)) {
            $coverUrl = trim($m[1]);
        } elseif (preg_match('/<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']/i', $html, $m)) {
            $coverUrl = trim($m[1]);
        } elseif (preg_match('/<meta\s+name=["\']twitter:image["\']\s+content=["\']([^"\']+)["\']/i', $html, $m)) {
            $coverUrl = trim($m[1]);
        }

        // Si viene en srcset con múltiples resoluciones, tomar la primera URL limpia
        if ($coverUrl && str_contains($coverUrl, ' ')) {
            $parts = explode(' ', trim($coverUrl));
            $coverUrl = $parts[0];
        }

        if ($this->isSiteLogoOrInvalid($coverUrl)) {
            $coverUrl = '';
        }

        if ($coverUrl) {
            if (str_starts_with($coverUrl, '//')) {
                $coverUrl = 'https:' . $coverUrl;
            } elseif (!str_starts_with($coverUrl, 'http')) {
                $coverUrl = 'https://www.romspedia.com' . (str_starts_with($coverUrl, '/') ? '' : '/') . $coverUrl;
            }
        }

        // Metadatos de la tabla view-emulator-detail
        $details = [];
        if (preg_match_all('/<div class="view-emulator-detail-name">\s*(.*?)\s*<\/div>\s*<div class="view-emulator-detail-value">\s*(.*?)\s*<\/div>/is', $html, $dm)) {
            for ($i = 0; $i < count($dm[1]); $i++) {
                $key = strtolower(trim(str_replace(':', '', strip_tags($dm[1][$i]))));
                $val = trim(strip_tags($dm[2][$i]));
                $details[$key] = $val;
            }
        }

        // Plataforma
        $platform = 'PlayStation Portable (PSP)';
        $platformSlug = 'psp';
        if (preg_match('/roms\/([a-z0-9\-]+)\//i', $url, $pm)) {
            $detected = $this->mapPlatform($pm[1]);
            $platform = $detected['name'];
            $platformSlug = $detected['slug'];
        } elseif (!empty($details['console'])) {
            $detected = $this->mapPlatform($details['console']);
            $platform = $detected['name'];
            $platformSlug = $detected['slug'];
        }

        // Categoría / Género
        $rawCategory = $details['category'] ?? 'Action';
        $categoryMapping = $this->mapCategories($rawCategory);

        // Año y Región
        $releaseYear = !empty($details['release year']) ? (int)$details['release year'] : null;
        $region = !empty($details['region']) ? $details['region'] : 'USA';

        // Encontrar enlace de subpágina de descarga
        $downloadPageUrl = '';
        if (preg_match('/<a[^>]+href="([^">]*\/download[^">]*)"/i', $html, $m)) {
            $downloadPageUrl = $m[1];
        }
        if (!$downloadPageUrl) {
            $downloadPageUrl = rtrim($url, '/') . '/download';
        }
        if (!str_starts_with($downloadPageUrl, 'http')) {
            $downloadPageUrl = 'https://www.romspedia.com' . (str_starts_with($downloadPageUrl, '/') ? '' : '/') . $downloadPageUrl;
        }

        // Consultar la subpágina de descarga para extraer el enlace directo
        $dlRes = $this->safety->safeFetch($downloadPageUrl, 'romspedia');
        $downloadHtml = $dlRes['html'] ?? '';
        $directDownloadUrl = '';

        if ($downloadHtml) {
            if (preg_match('/href="([^"]*(?:downloads\.romspedia\.com\/roms\/[^"]+|\.(?:zip|7z|iso|cso)))"/i', $downloadHtml, $m)) {
                $directDownloadUrl = html_entity_decode($m[1]);
            } elseif (preg_match('/window\.location\.href\s*=\s*["\']([^"\']+)["\']/i', $downloadHtml, $m)) {
                $directDownloadUrl = html_entity_decode($m[1]);
            }
        }

        // Inspeccionar cabeceras
        $fileSize = 'Pendiente';
        $fileSizeBytes = 0;
        $fileFormat = 'ZIP';
        $isDownloadAvailable = false;
        $httpCode = 0;

        if ($directDownloadUrl) {
            $headInfo = $this->checkFile($directDownloadUrl);
            $httpCode = $headInfo['http_code'];
            if ($httpCode === 200) {
                $isDownloadAvailable = true;
                $fileSizeBytes = $headInfo['content_length'];
                $fileSize = $this->formatBytes($fileSizeBytes);
                $fileFormat = strtoupper(pathinfo(parse_url($directDownloadUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'ZIP');
            }
        }

        $duration = round((microtime(true) - $startTime) * 1000);

        return [
            'success' => true,
            'source_provider' => 'Romspedia',
            'title' => $title,
            'cover_url' => $coverUrl,
            'platform' => $platform,
            'platform_slug' => $platformSlug,
            'raw_genre' => $rawCategory,
            'category_ids' => $categoryMapping['ids'],
            'category_names' => $categoryMapping['names'],
            'release_year' => $releaseYear,
            'region' => $region,
            'languages' => 'English',
            'publisher' => $details['publisher'] ?? null,
            'source_url' => $url,
            'download_page_url' => $downloadPageUrl,
            'direct_download_url' => $directDownloadUrl,
            'is_download_available' => $isDownloadAvailable,
            'file_size' => $fileSize,
            'file_size_bytes' => $fileSizeBytes,
            'file_format' => $fileFormat,
            'screenshots' => [],
            'http_status' => $httpCode,
            'latency_ms' => $duration,
        ];
    }

    /**
     * Scraper especializado para CDRomance.org
     */
    protected function scrapeCdromance(string $url): array
    {
        $startTime = microtime(true);

        // 0. Comprobar pausa preventiva
        $cooldown = $this->safety->checkCooldown('cdromance');
        if ($cooldown) {
            return [
                'success' => false,
                'cooldown' => true,
                'remaining_seconds' => $cooldown['remaining_seconds'],
                'message' => $cooldown['message'],
            ];
        }

        $this->safety->applyPoliteThrottle('cdromance');

        $cookieFile = tempnam(sys_get_temp_dir(), 'cdr_');
        $headers = $this->safety->getRandomHeaders($url);

        // 1. Descargar página principal guardando cookies de sesión
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_TIMEOUT, 18);
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 429 || $httpCode === 403) {
            $this->safety->triggerCooldown('cdromance', 60, "El servidor devolvió HTTP {$httpCode}");
            @unlink($cookieFile);
            return [
                'success' => false,
                'cooldown' => true,
                'remaining_seconds' => 60,
                'message' => "Pausa de seguridad activada para CDRomance (HTTP {$httpCode}). Protegiendo tu IP.",
            ];
        }

        if (!$html || $httpCode >= 400) {
            @unlink($cookieFile);
            return [
                'success' => false,
                'message' => 'No se pudo conectar o descargar la información desde CDRomance.',
            ];
        }

        // Título limpio
        $title = '';
        if (preg_match('/<h1[^>]*class="entry-title"[^>]*>(.*?)<\/h1>/is', $html, $m)) {
            $title = trim(strip_tags($m[1]));
        } elseif (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $m)) {
            $title = trim(strip_tags($m[1]));
        }
        // Limpiar sufijos típicos de CDRomance (ej. "PSP ISO", "PS2 ISO", "PSX ISO")
        $title = preg_replace('/\s+(?:PSP|PS2|PSX|GameCube|SNES|GBA|NDS)\s+(?:ISO|ROM|Game|Download).*$/i', '', $title);

        // Portada ultra-robusta evitando logos de CDRomance (cdr-logo-phoenix, cdr-logo-900x272, etc.)
        $coverUrl = '';

        // 1. JSON-LD Yoast Schema primary image (la imagen auténtica de la carátula)
        if (preg_match('/"(?:url|contentUrl)":"([^"]+)".*?"#primaryimage"/is', $html, $m) || preg_match('/"@id":"[^"]*#primaryimage".*?"(?:url|contentUrl)":"([^"]+)"/is', $html, $m)) {
            $candidate = stripslashes(trim($m[1]));
            if (!$this->isSiteLogoOrInvalid($candidate)) {
                $coverUrl = $candidate;
            }
        }

        // 2. Contenedor .post-thumbnail o clase wp-post-image
        if (!$coverUrl && preg_match('/<div[^>]*class=["\'][^"\']*post-thumbnail[^"\']*["\'][^>]*>.*?<img[^>]+(?:data-src|src)=["\']([^"\']+)["\']/is', $html, $m)) {
            $candidate = trim($m[1]);
            if (!$this->isSiteLogoOrInvalid($candidate)) {
                $coverUrl = $candidate;
            }
        }
        if (!$coverUrl && preg_match('/<img[^>]+class=["\'][^"\']*wp-post-image[^"\']*["\'][^>]+(?:data-src|src)=["\']([^"\']+)["\']/is', $html, $m)) {
            $candidate = trim($m[1]);
            if (!$this->isSiteLogoOrInvalid($candidate)) {
                $coverUrl = $candidate;
            }
        }

        // 3. Contenedores de carátula destacados
        if (!$coverUrl && preg_match('/<div[^>]*class=["\'][^"\']*(?:featured-image|entry-featured|game-cover)[^"\']*["\'][^>]*>.*?<img[^>]+(?:data-src|src)=["\']([^"\']+)["\']/is', $html, $m)) {
            $candidate = trim($m[1]);
            if (!$this->isSiteLogoOrInvalid($candidate)) {
                $coverUrl = $candidate;
            }
        }

        // 4. Imagen en el artículo que mencione box, cover o front
        if (!$coverUrl && preg_match('/<img[^>]+(?:data-src|src)=["\']([^"\']*(?:box[0-9_-]*|covers?|front)[^"\']*\.(?:jpg|jpeg|png|webp))["\']/i', $html, $m)) {
            $candidate = trim($m[1]);
            if (!$this->isSiteLogoOrInvalid($candidate)) {
                $coverUrl = $candidate;
            }
        }

        // 5. OpenGraph o Twitter card siempre que NO sea logo del sitio
        if (!$coverUrl && preg_match('/<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']/i', $html, $m)) {
            $candidate = trim($m[1]);
            if (!$this->isSiteLogoOrInvalid($candidate)) {
                $coverUrl = $candidate;
            }
        }
        if (!$coverUrl && preg_match('/<meta\s+name=["\']twitter:image["\']\s+content=["\']([^"\']+)["\']/i', $html, $m)) {
            $candidate = trim($m[1]);
            if (!$this->isSiteLogoOrInvalid($candidate)) {
                $coverUrl = $candidate;
            }
        }

        if ($coverUrl && str_starts_with($coverUrl, '//')) {
            $coverUrl = 'https:' . $coverUrl;
        }

        // Parsear tabla estructurada "GAME INFORMATION" de CDRomance primero para conocer la plataforma
        $tableData = [];
        if (preg_match_all('/<tr>\s*<th>(.*?)<\/th>\s*<td>(.*?)<\/td>\s*<\/tr>/is', $html, $rows)) {
            for ($i = 0; $i < count($rows[1]); $i++) {
                $rawKey = strtolower(trim(strip_tags($rows[1][$i])));
                $rawVal = trim(strip_tags($rows[2][$i]));
                $tableData[$rawKey] = $rawVal;
            }
        }

        // 1. Plataforma / Consola detectada
        $platform = 'PlayStation Portable (PSP)';
        $platformSlug = 'psp';
        $consoleDetected = $tableData['console'] ?? '';
        if ($consoleDetected) {
            $detected = $this->mapPlatform($consoleDetected);
            $platform = $detected['name'];
            $platformSlug = $detected['slug'];
        } elseif (preg_match('/cdromance\.org\/([a-z0-9\-]+)\//i', $url, $pm)) {
            $detected = $this->mapPlatform($pm[1]);
            $platform = $detected['name'];
            $platformSlug = $detected['slug'];
        }

        // 6. Si CDRomance no tiene carátula propia (o solo tenía el logo), buscar carátula oficial en Romspedia con la plataforma ya detectada
        if (!$coverUrl) {
            $coverUrl = $this->searchRomspediaCover($title, $platformSlug);
        }

        // 2. Género y Mapeo a Categorías
        $rawGenre = $tableData['genre'] ?? '';
        if (!$rawGenre) {
            if (preg_match('/ep_filter_genre=([a-z0-9\-]+)/i', $html, $gm)) {
                $rawGenre = ucwords(str_replace('-', ' ', $gm[1]));
            } elseif (preg_match('/class="[^"]*genre-([a-z0-9\-]+)[^"]*"/i', $html, $gm)) {
                $rawGenre = ucwords(str_replace('-', ' ', $gm[1]));
            } else {
                $rawGenre = 'Action';
            }
        }
        $categoryMapping = $this->mapCategories($rawGenre);

        // 3. Región
        $region = 'USA';
        if (!empty($tableData['region'])) {
            $region = strtoupper(trim($tableData['region']));
        } elseif (preg_match('/ep_filter_region=([a-z0-9\-]+)/i', $html, $rm)) {
            $region = strtoupper($rm[1]);
        }

        // 4. Fecha y Año de Lanzamiento (Game Release)
        $releaseYear = null;
        $releaseDate = null;
        $rawRelease = $tableData['game release'] ?? ($tableData['release'] ?? '');
        if ($rawRelease) {
            if (preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/', $rawRelease, $dm)) {
                $releaseDate = $dm[0];
                $releaseYear = (int)$dm[1];
            } elseif (preg_match('/([0-9]{4})/', $rawRelease, $ym)) {
                $releaseYear = (int)$ym[1];
            }
        }

        // 5. Idiomas (Languages / Translations)
        $languages = $tableData['languages'] ?? ($tableData['language'] ?? '');
        if (!$languages) {
            if (preg_match('/Languages:\s*([^"\<\.]+)/i', $html, $lm)) {
                $languages = trim($lm[1]);
            } else {
                $languages = 'English';
            }
        }

        // 6. Publisher / Desarrollador
        $publisher = $tableData['publisher'] ?? null;
        $developer = $tableData['developer'] ?? null;

        // Extraer post_id para el endpoint AJAX
        $postId = null;
        if (preg_match('/data-id="([0-9]+)"/i', $html, $pMatch)) {
            $postId = $pMatch[1];
        }

        $directDownloadUrl = '';
        $fileSize = 'Pendiente';
        $fileSizeBytes = 0;
        $fileFormat = '7Z';
        $isDownloadAvailable = false;
        $dlHttpCode = 0;

        if ($postId) {
            $this->safety->applyPoliteThrottle('cdromance');
            $ajaxUrl = 'https://cdromance.org/wp-content/plugins/cdr-main/public/ajax.php';
            $ch = curl_init($ajaxUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['post_id' => $postId]));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_REFERER, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'X-Requested-With: XMLHttpRequest',
                'Origin: https://cdromance.org',
                'Accept: */*',
            ]);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $ajaxResponse = curl_exec($ch);
            curl_close($ch);

            // Extraer primer enlace directo del HTML devuelto por el AJAX
            if ($ajaxResponse && preg_match('/<a[^>]+href="([^">]+download\.php[^">]+)"/i', $ajaxResponse, $dlm)) {
                $directDownloadUrl = html_entity_decode($dlm[1]);
            }

            // Extraer tamaño si viene en la tabla
            if ($ajaxResponse && preg_match('/<div class="td">\s*([0-9\.]+\s*(?:MB|GB|KB))\s*<\/div>/i', $ajaxResponse, $sm)) {
                $fileSize = trim($sm[1]);
            }
        }

        @unlink($cookieFile);

        // Verificar cabeceras del enlace directo
        if ($directDownloadUrl) {
            $headInfo = $this->checkFile($directDownloadUrl);
            $dlHttpCode = $headInfo['http_code'];
            if ($dlHttpCode === 200) {
                $isDownloadAvailable = true;
                if ($headInfo['content_length'] > 0) {
                    $fileSizeBytes = $headInfo['content_length'];
                    $fileSize = $this->formatBytes($fileSizeBytes);
                }
                $fileFormat = '7Z';
                if (preg_match('/\.([a-z0-9]+)(?:&|$)/i', $directDownloadUrl, $extm)) {
                    $fileFormat = strtoupper($extm[1]);
                }
            }
        }

        // Extraer Capturas de Pantalla (Game Screenshots)
        $screenshots = [];
        if (preg_match('/(?:Game Screenshots|Screenshots).*?(?:<div[^>]*class="[^"]*games-loop[^"]*"[^>]*>|<div[^>]*id="lightgallery"[^>]*>)(.*?)<\/div>\s*<\/div>/is', $html, $sm)) {
            preg_match_all('/(?:data-src="([^">]+)"|<a[^>]+href="([^">]+\.(?:jpg|jpeg|png|webp))")/i', $sm[0], $sMatches);
            $urlsFound = array_filter(array_merge($sMatches[1], $sMatches[2]));
            $screenshots = array_values(array_unique($urlsFound));
        }

        if (empty($screenshots)) {
            preg_match_all('/<a[^>]+itemprop="screenshot"[^>]+href="([^">]+)"/i', $html, $sm2);
            if (!empty($sm2[1])) {
                $screenshots = array_values(array_unique($sm2[1]));
            }
        }

        $duration = round((microtime(true) - $startTime) * 1000);

        return [
            'success' => true,
            'source_provider' => 'CDRomance',
            'title' => $title,
            'cover_url' => $coverUrl,
            'platform' => $platform,
            'platform_slug' => $platformSlug,
            'raw_genre' => $rawGenre,
            'category_ids' => $categoryMapping['ids'],
            'category_names' => $categoryMapping['names'],
            'release_year' => $releaseYear,
            'release_date' => $releaseDate,
            'region' => $region,
            'languages' => $languages,
            'publisher' => $publisher,
            'developer' => $developer,
            'source_url' => $url,
            'download_page_url' => $url . '#download',
            'direct_download_url' => $directDownloadUrl,
            'is_download_available' => $isDownloadAvailable,
            'file_size' => $fileSize,
            'file_size_bytes' => $fileSizeBytes,
            'file_format' => $fileFormat,
            'screenshots' => $screenshots,
            'http_status' => $dlHttpCode,
            'latency_ms' => $duration,
        ];
    }

    /**
     * Mapea un texto de género a las categorías locales de la base de datos.
     * Si el género no existe, lo crea automáticamente con slug, color e icono.
     */
    protected function mapCategories(string $rawGenre): array
    {
        $raw = mb_strtolower(trim($rawGenre));
        $ids = [];

        // 1. Detección por palabras clave para las categorías estándar
        if (preg_match('/action|aventura|adventure|hack|beat|slash/i', $raw)) {
            $cat = Category::where('slug', 'accion-aventura')->first();
            if ($cat) $ids[] = $cat->id;
        }
        if (preg_match('/rpg|role|jrpg|dungeon|fantasy/i', $raw)) {
            $cat = Category::where('slug', 'rpg-jrpg')->first();
            if ($cat) $ids[] = $cat->id;
        }
        if (preg_match('/platform|plataforma/i', $raw)) {
            $cat = Category::where('slug', 'plataformas')->first();
            if ($cat) $ids[] = $cat->id;
        }
        if (preg_match('/fight|lucha|brawl|versus/i', $raw)) {
            $cat = Category::where('slug', 'lucha')->first();
            if ($cat) $ids[] = $cat->id;
        }
        if (preg_match('/shoot|fps|tps|gun/i', $raw)) {
            $cat = Category::where('slug', 'shooter-fps')->first();
            if ($cat) $ids[] = $cat->id;
        }
        if (preg_match('/rac|carrera|driv|conducci/i', $raw)) {
            $cat = Category::where('slug', 'carreras')->first();
            if ($cat) $ids[] = $cat->id;
        }
        if (preg_match('/horror|terror|survival|zombie/i', $raw)) {
            $cat = Category::where('slug', 'terror-survival')->first();
            if ($cat) $ids[] = $cat->id;
        }
        if (preg_match('/strateg|tactic|estrategia/i', $raw)) {
            $cat = Category::where('slug', 'estrategia')->first();
            if ($cat) $ids[] = $cat->id;
        }
        if (preg_match('/sport|deporte|soccer|fifa|racing|nba|wrestling/i', $raw)) {
            $cat = Category::where('slug', 'deportes')->first();
            if ($cat) $ids[] = $cat->id;
        }

        // 2. Si no coincide con ninguna palabra clave, buscar o crear la categoría automáticamente
        $cleanName = trim(ucwords(preg_replace('/[_\-]+/', ' ', $rawGenre)));
        if (!empty($cleanName) && empty($ids)) {
            $slug = Str::slug($cleanName);
            $existing = Category::where('slug', $slug)->orWhere('name', 'LIKE', $cleanName)->first();
            if ($existing) {
                $ids[] = $existing->id;
            } else {
                // 3. ¡Creación automática de la nueva categoría en la base de datos!
                $colorPalette = ['#3B82F6', '#8B5CF6', '#10B981', '#EF4444', '#F59E0B', '#06B6D4', '#EC4899', '#6366F1', '#14B8A6', '#F97316'];
                $hash = abs(crc32($slug));
                $randomColor = $colorPalette[$hash % count($colorPalette)];

                $newCat = Category::create([
                    'name' => $cleanName,
                    'slug' => $slug,
                    'color' => $randomColor,
                    'icon' => 'tag',
                    'description' => "Juegos y ROMs clásicos del género {$cleanName}.",
                ]);
                $ids[] = $newCat->id;
            }
        }

        // Si todavía está vacío, asignar Acción & Aventura
        if (empty($ids)) {
            $fallback = Category::where('slug', 'accion-aventura')->first() ?? Category::first();
            if ($fallback) {
                $ids[] = $fallback->id;
            }
        }

        $ids = array_values(array_unique($ids));

        try {
            $names = Category::whereIn('id', $ids)->pluck('name')->toArray();
        } catch (\Throwable $e) {
            $names = ['Acción & Aventura'];
        }

        return [
            'ids' => $ids,
            'names' => $names,
        ];
    }

    /**
     * Mapea el slug de una plataforma a los de tu base de datos
     */
    protected function mapPlatform(string $slug): array
    {
        $slug = strtolower(trim($slug));

        $map = [
            'psp' => ['name' => 'PlayStation Portable (PSP)', 'slug' => 'psp'],
            'playstation-portable' => ['name' => 'PlayStation Portable (PSP)', 'slug' => 'psp'],
            'ps2' => ['name' => 'PlayStation 2 (PS2)', 'slug' => 'playstation-2'],
            'ps2-iso' => ['name' => 'PlayStation 2 (PS2)', 'slug' => 'playstation-2'],
            'playstation-2' => ['name' => 'PlayStation 2 (PS2)', 'slug' => 'playstation-2'],
            'psx' => ['name' => 'PlayStation (PS1)', 'slug' => 'playstation'],
            'psx-iso' => ['name' => 'PlayStation (PS1)', 'slug' => 'playstation'],
            'playstation' => ['name' => 'PlayStation (PS1)', 'slug' => 'playstation'],
            'gamecube' => ['name' => 'Nintendo GameCube', 'slug' => 'gamecube'],
            'gamecube-iso' => ['name' => 'Nintendo GameCube', 'slug' => 'gamecube'],
            'nds' => ['name' => 'Nintendo DS', 'slug' => 'nintendo-ds'],
            'nds-roms' => ['name' => 'Nintendo DS', 'slug' => 'nintendo-ds'],
            'nintendo-ds' => ['name' => 'Nintendo DS', 'slug' => 'nintendo-ds'],
            '3ds' => ['name' => 'Nintendo 3DS', 'slug' => 'nintendo-3ds'],
            'nintendo-3ds' => ['name' => 'Nintendo 3DS', 'slug' => 'nintendo-3ds'],
            'gba' => ['name' => 'Game Boy Advance', 'slug' => 'game-boy-advance'],
            'gba-rom' => ['name' => 'Game Boy Advance', 'slug' => 'game-boy-advance'],
            'gameboy-advance' => ['name' => 'Game Boy Advance', 'slug' => 'game-boy-advance'],
            'gb' => ['name' => 'Game Boy / GBC', 'slug' => 'game-boy'],
            'gbc' => ['name' => 'Game Boy / GBC', 'slug' => 'game-boy'],
            'game-boy' => ['name' => 'Game Boy / GBC', 'slug' => 'game-boy'],
            'snes' => ['name' => 'Super Nintendo (SNES)', 'slug' => 'snes'],
            'snes-rom' => ['name' => 'Super Nintendo (SNES)', 'slug' => 'snes'],
            'super-nintendo' => ['name' => 'Super Nintendo (SNES)', 'slug' => 'snes'],
            'nes' => ['name' => 'Nintendo Entertainment System', 'slug' => 'nes'],
            'nes-rom' => ['name' => 'Nintendo Entertainment System', 'slug' => 'nes'],
            'n64' => ['name' => 'Nintendo 64', 'slug' => 'nintendo-64'],
            'n64-roms' => ['name' => 'Nintendo 64', 'slug' => 'nintendo-64'],
            'nintendo-64' => ['name' => 'Nintendo 64', 'slug' => 'nintendo-64'],
            'dreamcast' => ['name' => 'Sega Dreamcast', 'slug' => 'dreamcast'],
            'dreamcast-iso' => ['name' => 'Sega Dreamcast', 'slug' => 'dreamcast'],
            'genesis' => ['name' => 'Sega Genesis / Mega Drive', 'slug' => 'sega-genesis'],
            'sega-genesis' => ['name' => 'Sega Genesis / Mega Drive', 'slug' => 'sega-genesis'],
            'wii' => ['name' => 'Nintendo Wii', 'slug' => 'wii'],
            'wii-iso' => ['name' => 'Nintendo Wii', 'slug' => 'wii'],
            'switch' => ['name' => 'Nintendo Switch', 'slug' => 'nintendo-switch'],
            'nintendo-switch' => ['name' => 'Nintendo Switch', 'slug' => 'nintendo-switch'],
        ];

        return $map[$slug] ?? [
            'name' => ucwords(str_replace('-', ' ', $slug)),
            'slug' => $slug,
        ];
    }

    /**
     * Realiza una petición GET segura a través de ScraperSafetyService
     */
    protected function fetch(string $url): ?string
    {
        $provider = str_contains($url, 'cdromance') ? 'cdromance' : 'romspedia';
        $res = $this->safety->safeFetch($url, $provider);
        return $res['html'] ?? null;
    }

    /**
     * Comprueba las cabeceras HTTP del archivo remoto
     */
    protected function checkFile(string $url): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->safety->getRandomHeaders($url));
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);

        return [
            'http_code' => $httpCode,
            'content_length' => $contentLength > 0 ? (int)$contentLength : 0,
        ];
    }

    /**
     * Convierte bytes a formato legible (MB, GB)
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    /**
     * Comprueba si una URL es un logo del sitio, banner, avatar o placeholder inválido
     */
    public function isSiteLogoOrInvalid(?string $url): bool
    {
        if (empty($url)) {
            return true;
        }

        if (preg_match('/(?:cdr-logo|logo|banner|header|phoenix|avatar|favicon|icon|spinner|thumb(?:nail)?-unavailable)/i', $url)) {
            return true;
        }

        return false;
    }

    /**
     * Busca una carátula oficial HD 3D en Romspedia basada en título y plataforma
     */
    public function searchRomspediaCover(string $title, string $platformSlug = ''): ?string
    {
        $cleanSearch = trim(preg_replace('/\s*(?:\([^)]*\)|\[[^\]]*\])/', '', $title));
        $cleanSearch = trim(preg_replace('/\s+(?:ROM|ISO|Download|Game).*$/i', '', $cleanSearch));
        if (empty($cleanSearch)) {
            return null;
        }

        $url = 'https://www.romspedia.com/search?search_term_string=' . urlencode($cleanSearch);

        $res = $this->safety->safeFetch($url, 'romspedia', [], true, 3600);
        if (!$res['success'] || empty($res['html'])) {
            return null;
        }

        $html = $res['html'];

        // Solo buscar dentro de los items de resultado <div class="single-rom">
        if (!preg_match_all('/<div class="single-rom">(.*?)<\/div>\s*<\/div>\s*<\/a>/is', $html, $matches)) {
            return null;
        }

        $bestCandidate = null;
        $highestScore = 0;

        foreach ($matches[1] as $card) {
            $cardTitle = '';
            if (preg_match('/<h2 class="roms-title">([^<]+)<\/h2>/i', $card, $tm)) {
                $cardTitle = trim(html_entity_decode($tm[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }

            $cardCover = '';
            if (preg_match('/(?:srcset|src)=["\']([^"\'\s]*static\.romspedia\.com\/webp\/roms\/[^"\'\s]*cover[^"\'\s]*\.webp)/i', $card, $im)) {
                $cardCover = $im[1];
            }

            if (!$cardTitle || !$cardCover || $this->isSiteLogoOrInvalid($cardCover)) {
                continue;
            }

            $cardPlatform = '';
            if (preg_match('/<a href="\/roms\/([^"]+)"[^>]*class="[^"]*emulator[^"]*"/i', $card, $pm)) {
                $cardPlatform = $pm[1];
            }

            similar_text(strtolower($cleanSearch), strtolower($cardTitle), $percent);
            if ($platformSlug && $cardPlatform && (str_contains(strtolower($platformSlug), strtolower($cardPlatform)) || str_contains(strtolower($cardPlatform), strtolower($platformSlug)))) {
                $percent += 15;
            }

            // Exigir al menos 40% de coincidencia para no traer juegos no relacionados de footer
            if ($percent > $highestScore && $percent >= 40) {
                $highestScore = $percent;
                $bestCandidate = $cardCover;
            }
        }

        return $bestCandidate;
    }
}
