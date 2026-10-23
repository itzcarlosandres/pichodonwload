<?php

namespace App\Services;

use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiContentService
{
    public const PROVIDER_GROQ = 'groq';

    public const PROVIDER_GEMINI = 'gemini';

    public const DEFAULT_PROVIDER = self::PROVIDER_GROQ;

    public const GROQ_DEFAULT_MODEL = 'qwen/qwen3.8-27b';

    public const GROQ_MODELS = [
        'qwen/qwen3.8-27b' => 'Groq: Qwen 3.8 27B (Recomendado — Excelente en Español & Sinopsis Extensas)',
        'openai/gpt-oss-120b' => 'Groq: GPT-OSS 120B (Alta Capacidad de Razonamiento)',
        'openai/gpt-oss-20b' => 'Groq: GPT-OSS 20B (Ultra Ligero e Instantáneo)',
    ];

    public const GEMINI_DEFAULT_MODEL = 'gemini-3.8-flash';

    public const GEMINI_MODELS = [
        'gemini-3.8-flash' => 'Google Gemini 3.8 Flash (Oficial — Recomendado & Estable)',
        'gemini-3.8-pro' => 'Google Gemini 3.8 Pro (Máxima Potencia & Razonamiento)',
        'gemini-3.0-flash' => 'Google Gemini 3.0 Flash',
        'gemini-3.0-pro' => 'Google Gemini 3.0 Pro',
        'gemini-2.5-flash' => 'Google Gemini 2.5 Flash',
        'gemini-2.5-pro' => 'Google Gemini 2.5 Pro',
    ];

    // Retrocompatibilidad
    public const DEFAULT_MODEL = self::GROQ_DEFAULT_MODEL;

    public const SUPPORTED_MODELS = self::GROQ_MODELS;

    /**
     * Resuelve dinámicamente el nombre de la plataforma o dominio activo
     */
    public function getPlatformBrand(): string
    {
        // 1. Dominio explícito configurado en los ajustes del sistema
        $configuredDomain = Setting::get('site_domain');
        if (! empty($configuredDomain)) {
            return trim($configuredDomain);
        }

        // 2. Dominio real de la petición web en vivo (ej. pichodownload.com)
        if (app()->bound('request') && request()?->getHost()) {
            $host = request()->getHost();
            if ($host && ! in_array($host, ['localhost', '127.0.0.1'])) {
                return $host;
            }
        }

        // 3. Dominio configurado en .env (APP_URL)
        $appUrl = config('app.url');
        if (! empty($appUrl)) {
            $parsedHost = parse_url($appUrl, PHP_URL_HOST);
            if ($parsedHost && ! in_array($parsedHost, ['localhost', '127.0.0.1'])) {
                return $parsedHost;
            }
        }

        // 4. Nombre configurado en Ajustes (Settings > site_name)
        $siteName = Setting::get('site_name');
        if (! empty($siteName)) {
            return trim($siteName);
        }

        // 5. Fallback a config('app.name')
        return config('app.name', 'PichoDownload');
    }

    /**
     * Genera automáticamente Meta Title y Meta Description optimizados para SEO y CTR
     */
    public function generateSeo(string $title, string $consoleName, array $context = []): array
    {
        $brand = $this->getPlatformBrand();
        $contextLines = [];
        if (! empty($context['release_year'])) {
            $contextLines[] = '- Año de lanzamiento: '.$context['release_year'];
        }
        if (! empty($context['genre'])) {
            $contextLines[] = '- Género: '.$context['genre'];
        }
        if (! empty($context['region'])) {
            $contextLines[] = '- Región: '.$context['region'];
        }
        if (! empty($context['languages'])) {
            $contextLines[] = '- Idiomas disponibles: '.$context['languages'];
        }
        if (! empty($context['publisher'])) {
            $contextLines[] = '- Publicador: '.$context['publisher'];
        }

        $contextText = ! empty($contextLines)
            ? "\nDATOS TÉCNICOS VERIFICADOS DEL VIDEOJUEGO:\n".implode("\n", $contextLines)."\n"
            : '';

        $hasSpanish = ! empty($context['languages']) && (stripos($context['languages'], 'span') !== false || stripos($context['languages'], 'españ') !== false);
        $langHint = $hasSpanish ? "Menciona 'en Español' en el título o descripción para disparar el CTR si es relevante." : '';

        $prompt = "Eres un experto en SEO para la plataforma de preservación y emulación de videojuegos {$brand}.\n"
            ."Para el juego '{$title}' de la consola '{$consoleName}', genera un JSON válido con la siguiente estructura exacta:\n"
            ."{\n"
            .'  "meta_title": "Título SEO persuasivo (máximo 60 caracteres)",'."\n"
            .'  "meta_description": "Descripción atractiva con llamada a la acción y compatibilidad de emuladores (máximo 155 caracteres)",'."\n"
            .'  "keywords": "palabras clave separadas por comas"'."\n"
            ."}\n"
            .$contextText
            ."REGLAS SEO:\n"
            ."- Aprovecha los datos técnicos reales como el año o el género.\n"
            .($langHint ? "- {$langHint}\n" : '')
            .'Responde ÚNICAMENTE con el objeto JSON puro sin bloques de código ni texto adicional.';

        $rawResponse = $this->callAiApi($prompt, true);

        try {
            $cleanJson = trim($rawResponse);
            if (preg_match('/\{.*\}/s', $cleanJson, $matches)) {
                $cleanJson = $matches[0];
            }
            $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', $cleanJson);
            $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);
            $data = json_decode($cleanJson, true);

            if (is_array($data) && ! empty($data['meta_title'])) {
                return [
                    'success' => true,
                    'meta_title' => trim($data['meta_title']),
                    'meta_description' => ! empty($data['meta_description']) ? trim($data['meta_description']) : "Descarga {$title} para {$consoleName} en alta definición en {$brand}. ROM verificada y compatible con emuladores.",
                    'keywords' => $data['keywords'] ?? "{$title}, {$consoleName}, rom, iso, emulador",
                ];
            }
        } catch (Exception $e) {
            // Fallback gracefully
        }

        // Heurística de respaldo dinámica si la IA no estuviese disponible
        $genrePart = ! empty($context['genre']) ? " de {$context['genre']}" : '';
        $yearPart = ! empty($context['release_year']) ? " ({$context['release_year']})" : '';
        $extraTitle = $hasSpanish ? ' en Español' : '';

        return [
            'success' => true,
            'meta_title' => "Descargar {$title}{$extraTitle} para {$consoleName} [ROM ISO]",
            'meta_description' => "Juega a {$title}{$yearPart}{$extraTitle} en {$consoleName}. Archivo verificado{$genrePart} listo para emular en alta velocidad en {$brand}.",
            'keywords' => "{$title}, {$consoleName}, descargar rom {$title}, iso {$consoleName}, emulador {$consoleName}",
        ];
    }

    /**
     * Genera una sinopsis y análisis técnico enriquecido en Markdown con Gemini
     */
    public function generateRichDescription(string $title, string $consoleName, array $context = []): array
    {
        $brand = $this->getPlatformBrand();
        $contextLines = [];
        if (! empty($context['release_year'])) {
            $contextLines[] = '- Año de lanzamiento original: '.$context['release_year'];
        }
        if (! empty($context['genre'])) {
            $contextLines[] = '- Género: '.$context['genre'];
        }
        if (! empty($context['region'])) {
            $contextLines[] = '- Región de la edición: '.$context['region'];
        }
        if (! empty($context['languages'])) {
            $contextLines[] = '- Idiomas incluidos: '.$context['languages'];
        }
        if (! empty($context['publisher'])) {
            $contextLines[] = '- Publicador: '.$context['publisher'];
        }
        if (! empty($context['developer'])) {
            $contextLines[] = '- Desarrollador: '.$context['developer'];
        }

        $contextText = ! empty($contextLines)
            ? "\nFICHA TÉCNICA VERIFICADA:\n".implode("\n", $contextLines)."\nUsa estos datos técnicos exactos sin cambiarlos ni inventar fechas, consolas o estudios erróneos.\n"
            : '';

        $prompt = "Actúa como redactor y crítico profesional de videojuegos para la plataforma digital {$brand}.\n"
            ."Escribe un artículo, análisis y sinopsis profunda en español para el videojuego '{$title}' en su versión de {$consoleName}.\n\n"
            .$contextText."\n"
            ."REQUISITOS ESTRICTOS DE EXTENSIÓN Y FORMATO:\n"
            ."- LONGITUD MÍNIMA OBLIGATORIA: 200 a 260 palabras. NO hagas un resumen corto; desarrolla cada uno de los 3 párrafos de forma extensa y detallada.\n"
            ."- ESTRUCTURA: Redacta exactamente 3 párrafos completos y bien cohesionados:\n"
            ."  * Párrafo 1 (Contexto y Universo): Premisa argumental, ambientación, atmósfera y punto de partida de la aventura.\n"
            ."  * Párrafo 2 (Jugabilidad y Tecnologías): Mecánicas principales, dinámicas de combate/control, ritmo de juego y cómo aprovecha el hardware de {$consoleName}.\n"
            ."  * Párrafo 3 (Legado y Preservación): Impacto en la industria, recepción crítica y su importancia histórica como joya imprescindible para emular y preservar en {$brand}.\n"
            ."- Usa **negrita** para resaltar títulos, mecánicas, modos y personajes clave.\n"
            ."- Si citas la plataforma web en el párrafo de preservación, utiliza siempre **{$brand}**.\n\n"
            ."REGLAS:\n"
            ."- Empieza DIRECTAMENTE con el texto del primer párrafo (sin títulos, sin 'Introducción:', sin saludos ni preámbulos).";

        $rawContent = $this->callAiApi($prompt);

        if (! empty($rawContent)) {
            $content = trim($rawContent);

            // Eliminar preámbulos conversacionales comunes de IA
            $content = preg_replace('/^(?:Aquí tienes|A continuación|Por supuesto|Claro que sí|Hola|Ficha Detallada|Bienvenido|Te presento)[^\n]*\n+/imu', '', $content);

            // Eliminar encabezados iniciales redundantes si la IA los incluyó
            for ($i = 0; $i < 5; $i++) {
                $prev = $content;
                $content = preg_replace('/^\s*#+\s*(?:Ficha|Sinopsis|Argumento|Contexto|\d+\.)[^\n]*\n+/imu', '', $content);
                $content = preg_replace('/^#\s+[^\n]+\n+/u', '', $content);
                $content = preg_replace('/^[\s\-=*_]{3,}\n+/u', '', $content);
                $content = ltrim($content);
                if ($content === $prev) {
                    break;
                }
            }

            $content = trim($content);

            // Garantía de extensión mínima: Si la IA produjo menos de 120 palabras, enriquecer con análisis técnico y preservación
            $wordCount = str_word_count(strip_tags($content));
            if ($wordCount < 120) {
                $content .= "\n\nA nivel de diseño y mecánicas en **{$consoleName}**, la experiencia sobresale por una jugabilidad pulida, controles precisos y un ritmo perfectamente equilibrado que premia tanto la exploración como la maestría en cada desafío. Sus innovaciones jugables se complementan con un apartado sonoro y visual sobresaliente que aprovecha al máximo las capacidades de su generación.\n\n"
                    ."Actualmente, esta entrega se consolida como una obra imprescindible dentro de la historia del videojuego. A través de la preservación digital en **{$brand}** y la compatibilidad con emuladores de alta fidelidad a **60 FPS**, los jugadores pueden redescubrir este clásico legendario en su máxima expresión técnica.";
            }
        } else {
            $genreText = ! empty($context['genre']) ? "dentro del género **{$context['genre']}**" : 'en su género';
            $yearText = ! empty($context['release_year']) ? "originalmente en {$context['release_year']}" : 'durante su época dorada';
            $devText = ! empty($context['developer']) ? "desarrollado por **{$context['developer']}**" : 'diseñado';
            $langText = ! empty($context['languages']) ? "Disponible en {$context['languages']}." : '';

            $content = "**{$title}** es una destacada entrega de **{$consoleName}**, {$devText} y publicada {$yearText} {$genreText}. La propuesta sumerge a los jugadores en una experiencia inmersiva con desafíos diseñados para aprovechar las particularidades del sistema.\n\n"
                ."A nivel jugable, destaca por sus mecánicas de control accesibles y una curva de dificultad bien calibrada. Sus escenarios y dinámicas de juego invitan a la habilidad constante y a explorar al máximo las virtudes del catálogo de **{$consoleName}**. {$langText}\n\n"
                ."Para la comunidad de emulación y preservación digital en **{$brand}**, este título representa una pieza indispensable. El archivo se encuentra verificado para garantizar la máxima compatibilidad y rendimiento fluido a **60 FPS** en los emuladores recomendados.";
        }

        return [
            'success' => true,
            'description' => $content,
        ];
    }

    /**
     * Autocompleta especificaciones técnicas sugeridas
     */
    public function autocompleteSpecs(string $title, string $consoleName): array
    {
        $prompt = "Para el juego '{$title}' de la consola '{$consoleName}', devuelve un JSON con datos técnicos reales conocidos:\n"
            ."{\n"
            .'  "release_year": 2005,'."\n"
            .'  "developer": "Nombre del Desarrollador",'."\n"
            .'  "publisher": "Nombre del Publicador",'."\n"
            .'  "file_format": "ISO"'."\n"
            ."}\n"
            .'Responde únicamente con el JSON puro.';

        $rawResponse = $this->callAiApi($prompt);

        try {
            $cleanJson = trim($rawResponse);
            $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', $cleanJson);
            $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);
            $data = json_decode($cleanJson, true);

            if (is_array($data)) {
                return [
                    'success' => true,
                    'release_year' => $data['release_year'] ?? date('Y'),
                    'developer' => $data['developer'] ?? 'Nintendo / Sony / Sega',
                    'publisher' => $data['publisher'] ?? 'Official Publisher',
                    'file_format' => $data['file_format'] ?? 'ISO',
                ];
            }
        } catch (Exception $e) {
            // Fallback
        }

        return [
            'success' => true,
            'release_year' => 2004,
            'developer' => 'Desarrollador Oficial',
            'publisher' => 'Publicador Oficial',
            'file_format' => 'ISO',
        ];
    }

    /**
     * Resuelve el proveedor activo (groq o gemini)
     */
    public function getActiveProvider(?string $apiKey = null, ?string $provider = null): string
    {
        $key = $apiKey ?: Setting::get('ai_api_key');
        if (empty($key)) {
            $key = env('GROQ_API_KEY', env('GEMINI_API_KEY', ''));
        }

        // Si la clave tiene un prefijo inequívoco, manda el tipo de clave
        if (! empty($key)) {
            if (str_starts_with($key, 'gsk_')) {
                return self::PROVIDER_GROQ;
            }
            if (str_starts_with($key, 'AIza')) {
                return self::PROVIDER_GEMINI;
            }
        }

        if (! empty($provider)) {
            return strtolower($provider);
        }

        $configuredProvider = Setting::get('ai_provider');
        if (! empty($configuredProvider)) {
            return strtolower($configuredProvider);
        }

        return self::DEFAULT_PROVIDER;
    }

    /**
     * Prueba de conexión en vivo con el proveedor de IA (Groq o Google Gemini)
     */
    public function testConnection(?string $apiKey = null, ?string $model = null, ?string $provider = null): array
    {
        $apiKey = $apiKey ?: Setting::get('ai_api_key');
        $resolvedProvider = $this->getActiveProvider($apiKey, $provider);
        $brand = $this->getPlatformBrand();

        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'La clave API no está configurada.',
            ];
        }

        if ($resolvedProvider === self::PROVIDER_GROQ) {
            $model = $model ?: Setting::get('ai_model', self::GROQ_DEFAULT_MODEL);

            try {
                $start = microtime(true);
                $response = Http::withoutVerifying()
                    ->withToken($apiKey)
                    ->timeout(15)
                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model' => $model,
                        'messages' => [
                            ['role' => 'user', 'content' => "Di exactamente: \"Conexión exitosa con Groq Cloud para {$brand}.\""],
                        ],
                        'temperature' => 0.1,
                        'max_tokens' => 80,
                    ]);

                $duration = round((microtime(true) - $start) * 1000);

                if ($response->successful()) {
                    $reply = $response->json('choices.0.message.content') ?? 'Conexión OK';

                    return [
                        'success' => true,
                        'provider' => 'groq',
                        'model' => $model,
                        'latency_ms' => $duration,
                        'message' => "¡Conexión exitosa con Groq Cloud ({$duration}ms)! Modelo: {$model}. Respuesta: ".trim($reply),
                    ];
                }

                $errorMsg = $response->json('error.message') ?? 'HTTP '.$response->status();

                return [
                    'success' => false,
                    'message' => 'Error de Groq API: '.$errorMsg,
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Excepción de red con Groq: '.$e->getMessage(),
                ];
            }
        }

        // Proveedor: Google Gemini
        $model = $model ?: Setting::get('ai_model', self::GEMINI_DEFAULT_MODEL);

        try {
            $start = microtime(true);
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            $response = Http::withoutVerifying()->timeout(15)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "Di exactamente: \"Conexión exitosa con Google Gemini API para {$brand}.\""],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 60,
                ],
            ]);

            $duration = round((microtime(true) - $start) * 1000);

            if ($response->successful()) {
                $reply = $response->json('candidates.0.content.parts.0.text') ?? 'Conexión OK';

                return [
                    'success' => true,
                    'provider' => 'gemini',
                    'model' => $model,
                    'latency_ms' => $duration,
                    'message' => "¡Conexión exitosa con Google Gemini ({$duration}ms)! Respuesta: ".trim($reply),
                ];
            }

            $errorMsg = $response->json('error.message') ?? 'HTTP '.$response->status();

            return [
                'success' => false,
                'message' => 'Error de Gemini API: '.$errorMsg,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Excepción de red con Gemini: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Genera automáticamente metadatos, palabras clave de búsqueda y diseño para una Saga de videojuegos
     */
    public function generateFranchiseData(string $franchiseName): array
    {
        $brand = $this->getPlatformBrand();
        $prompt = "Eres un historiador de videojuegos y diseñador UI para la plataforma de emulación {$brand}.\n"
            ."Para la franquicia o saga de videojuegos '{$franchiseName}', genera un JSON válido con la siguiente estructura exacta:\n"
            ."{\n"
            .'  "subtitle": "Un subtítulo épico de 6 a 10 palabras sobre la saga",'."\n"
            .'  "description": "Una breve reseña de 2 a 3 oraciones sobre el impacto y legado de la saga",'."\n"
            .'  "color": "Color hex representativo de la saga (ej. #EAB308, #EF4444, #3B82F6, #10B981, #8B5CF6, #F97316)",'."\n"
            .'  "icon": "Nombre de ícono Lucide representativo (ej. sword, shield, flame, sparkles, skull, crosshair, zap, gauge, trophy, gamepad-2, box, crown)",'."\n"
            .'  "search_terms": "Lista de nombres alternativos, variantes ortográficas y subtítulos clave para encontrar todos sus juegos (separados por coma. Ejemplo: Final Fantasy, Crisis Core, Dirge of Cerberus)"'."\n"
            ."}\n"
            .'Responde ÚNICAMENTE con el objeto JSON puro sin bloques markdown de código.';

        $rawResponse = $this->callAiApi($prompt);

        try {
            $cleanJson = trim($rawResponse);
            $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', $cleanJson);
            $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);
            $data = json_decode($cleanJson, true);

            if (is_array($data) && isset($data['subtitle'])) {
                return [
                    'success' => true,
                    'subtitle' => $data['subtitle'] ?? "Colección legendaria de {$franchiseName}",
                    'description' => $data['description'] ?? "Explora todas las entregas clásicas y modernas de la saga {$franchiseName} preservadas en alta definición.",
                    'color' => $data['color'] ?? '#CE2D2D',
                    'icon' => $data['icon'] ?? 'sparkles',
                    'search_terms' => $data['search_terms'] ?? $franchiseName,
                ];
            }
        } catch (Exception $e) {
            // Fallback
        }

        // Heuristic fallback if API is not configured or offline
        return [
            'success' => true,
            'subtitle' => "Colección legendaria y cronología de {$franchiseName}",
            'description' => "Explora y descarga todos los lanzamientos canónicos, spin-offs y entregas remasterizadas de {$franchiseName} preservadas en el Vault.",
            'color' => '#CE2D2D',
            'icon' => 'sparkles',
            'search_terms' => $franchiseName,
        ];
    }

    /**
     * Enrutador central para llamadas de IA (Groq o Google Gemini) con conmutación por error
     */
    protected function callAiApi(string $prompt, bool $jsonMode = false): string
    {
        $provider = $this->getActiveProvider();

        $content = '';
        if ($provider === self::PROVIDER_GROQ) {
            $content = $this->callGroqApi($prompt, $jsonMode);
            if (empty($content)) {
                $content = $this->callGeminiApi($prompt, $jsonMode);
            }
        } else {
            $content = $this->callGeminiApi($prompt, $jsonMode);
            if (empty($content)) {
                $content = $this->callGroqApi($prompt, $jsonMode);
            }
        }

        return $content;
    }

    /**
     * Llamada oficial a Groq Cloud API (Inferencia Ultra Rápida)
     */
    protected function callGroqApi(string $prompt, bool $jsonMode = false): string
    {
        $apiKey = Setting::get('ai_api_key');
        if (empty($apiKey) || ! str_starts_with($apiKey, 'gsk_')) {
            $apiKey = env('GROQ_API_KEY', '');
        }

        $model = Setting::get('ai_model', self::GROQ_DEFAULT_MODEL);
        if (empty($model) || ! str_contains($model, '/')) {
            $model = self::GROQ_DEFAULT_MODEL;
        }

        $temperature = (float) Setting::get('ai_temperature', 0.7);

        if (empty($apiKey)) {
            return '';
        }

        try {
            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => $temperature,
                'max_tokens' => 2048,
            ];

            if ($jsonMode) {
                $payload['response_format'] = ['type' => 'json_object'];
            }

            $response = Http::withoutVerifying()
                ->withToken($apiKey)
                ->connectTimeout(15)
                ->timeout(45)
                ->retry(2, 300)
                ->post('https://api.groq.com/openai/v1/chat/completions', $payload);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? '';
            }

            Log::warning('Groq API Error: '.$response->status().' - '.$response->body());
        } catch (Exception $e) {
            Log::warning('Groq API Exception: '.$e->getMessage());
        }

        return '';
    }

    /**
     * Llamada oficial directa a Google Gemini API (v1beta)
     */
    protected function callGeminiApi(string $prompt, bool $jsonMode = false): string
    {
        $apiKey = Setting::get('ai_api_key');
        if (empty($apiKey) || ! str_starts_with($apiKey, 'AIza')) {
            $apiKey = env('GEMINI_API_KEY', '');
        }

        $model = Setting::get('ai_model', self::GEMINI_DEFAULT_MODEL);
        if (empty($model) || str_contains($model, '/')) {
            $model = self::GEMINI_DEFAULT_MODEL;
        }

        $temperature = (float) Setting::get('ai_temperature', 0.7);

        if (empty($apiKey)) {
            return '';
        }

        try {
            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'maxOutputTokens' => 2048,
                ],
            ];

            if ($jsonMode) {
                $payload['generationConfig']['responseMimeType'] = 'application/json';
            }

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            $response = Http::withoutVerifying()
                ->connectTimeout(15)
                ->timeout(45)
                ->retry(2, 300)
                ->post($url, $payload);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text') ?? '';
            }

            Log::warning('Gemini API Error: '.$response->status().' - '.$response->body());
        } catch (Exception $e) {
            Log::warning('Gemini API Exception: '.$e->getMessage());
        }

        return '';
    }
}
