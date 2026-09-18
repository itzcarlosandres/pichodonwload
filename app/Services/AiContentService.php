<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Exception;

class AiContentService
{
    /**
     * Modelos recomendados de Google Gemini (versiones 2.5 y 3.0+)
     */
    public const DEFAULT_MODEL = 'gemini-2.5-flash';

    public const SUPPORTED_MODELS = [
        'gemini-2.5-flash' => 'Google Gemini 2.5 Flash (Recomendado — Ultra Rápido & Eficiente)',
        'gemini-2.5-pro'   => 'Google Gemini 2.5 Pro (Máxima Capacidad Analítica)',
        'gemini-3.0-flash' => 'Google Gemini 3.0 Flash (Nueva Generación 3.0)',
        'gemini-3.0-pro'   => 'Google Gemini 3.0 Pro (Alta Potencia y Razonamiento)',
        'gemini-2.0-flash' => 'Google Gemini 2.0 Flash (Equilibrado)',
        'gemini-1.5-flash' => 'Google Gemini 1.5 Flash (Legacy)',
    ];

    /**
     * Genera automáticamente Meta Title y Meta Description optimizados para SEO y CTR
     */
    public function generateSeo(string $title, string $consoleName): array
    {
        $prompt = "Eres un experto en SEO para plataformas de preservación de videojuegos.\n"
            . "Para el juego '{$title}' de la consola '{$consoleName}', genera un JSON válido con la siguiente estructura exacta:\n"
            . "{\n"
            . '  "meta_title": "Título SEO persuasivo (máximo 60 caracteres)",' . "\n"
            . '  "meta_description": "Descripción atractiva con llamada a la acción y compatibilidad de emuladores (máximo 155 caracteres)",' . "\n"
            . '  "keywords": "palabras clave separadas por comas"' . "\n"
            . "}\n"
            . "Responde ÚNICAMENTE con el objeto JSON puro sin bloques de código ni texto adicional.";

        $rawResponse = $this->callGeminiApi($prompt);

        try {
            $cleanJson = trim($rawResponse);
            if (preg_match('/\{.*\}/s', $cleanJson, $matches)) {
                $cleanJson = $matches[0];
            }
            $cleanJson = preg_replace('/^```(?:json)?\s*/i', '', $cleanJson);
            $cleanJson = preg_replace('/\s*```$/', '', $cleanJson);
            $data = json_decode($cleanJson, true);

            if (is_array($data) && !empty($data['meta_title'])) {
                return [
                    'success' => true,
                    'meta_title' => trim($data['meta_title']),
                    'meta_description' => !empty($data['meta_description']) ? trim($data['meta_description']) : "Descarga {$title} para {$consoleName} en alta definición. ROM verificada y compatible con emuladores.",
                    'keywords' => $data['keywords'] ?? "{$title}, {$consoleName}, rom, iso, emulador",
                ];
            }
        } catch (Exception $e) {
            // Fallback gracefully
        }

        // Default heuristic generation if Gemini key is not configured or fails
        return [
            'success' => true,
            'meta_title' => "Descargar {$title} {$consoleName} ROM ISO Verificado",
            'meta_description' => "Descarga {$title} para {$consoleName} con volcado verificado No-Intro/Redump. Guía de emulación y configuración a 60 FPS.",
            'keywords' => "{$title}, {$consoleName}, descargar rom {$title}, iso {$consoleName}, emulacion 60fps",
        ];
    }

    /**
     * Genera una sinopsis y análisis técnico enriquecido en Markdown con Gemini
     */
    public function generateRichDescription(string $title, string $consoleName): array
    {
        $prompt = "Actúa como redactor y crítico profesional de videojuegos para la enciclopedia de preservación digital ROMHUB.\n"
            . "Escribe un artículo, análisis y sinopsis profunda en español para el videojuego '{$title}' en su versión de {$consoleName}.\n\n"
            . "REQUISITOS ESTRICTOS DE EXTENSIÓN Y FORMATO:\n"
            . "- LONGITUD MÍNIMA OBLIGATORIA: 200 a 260 palabras. NO hagas un resumen corto; desarrolla cada uno de los 3 párrafos de forma extensa y detallada.\n"
            . "- ESTRUCTURA: Redacta exactamente 3 párrafos completos y bien cohesionados:\n"
            . "  * Párrafo 1 (Contexto y Universo): Premisa argumental, ambientación, atmósfera y punto de partida de la aventura.\n"
            . "  * Párrafo 2 (Jugabilidad y Tecnologías): Mecánicas principales, dinámicas de combate/control, ritmo de juego y cómo aprovecha el hardware de {$consoleName}.\n"
            . "  * Párrafo 3 (Legado y Preservación): Impacto en la industria, recepción crítica y su importancia histórica como joya imprescindible para emular y preservar.\n"
            . "- Usa **negrita** para resaltar títulos, mecánicas, modos y personajes clave.\n\n"
            . "REGLAS:\n"
            . "- Empieza DIRECTAMENTE con el texto del primer párrafo (sin títulos, sin 'Introducción:', sin saludos ni preámbulos).";

        $rawContent = $this->callGeminiApi($prompt);

        if (!empty($rawContent)) {
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
                if ($content === $prev) break;
            }

            $content = trim($content);

            // Garantía de extensión mínima: Si la IA produjo menos de 120 palabras, enriquecer con análisis técnico y preservación
            $wordCount = str_word_count(strip_tags($content));
            if ($wordCount < 120) {
                $content .= "\n\nA nivel de diseño y mecánicas en **{$consoleName}**, la experiencia sobresale por una jugabilidad pulida, controles precisos y un ritmo perfectamente equilibrado que premia tanto la exploración como la maestría en cada desafío. Sus innovaciones jugables se complementan con un apartado sonoro y visual sobresaliente que aprovecha al máximo las capacidades de su generación.\n\n"
                    . "Actualmente, esta entrega se consolida como una obra imprescindible dentro de la historia del videojuego. A través de la preservación digital en **ROMHUB** y la compatibilidad con emuladores de alta fidelidad a **60 FPS**, los jugadores pueden redescubrir este clásico legendario en su máxima expresión técnica.";
            }
        } else {
            $content = "Ambientado en un universo fascinante y diseñado con maestría técnica, **{$title}** representa uno de los hitos más emblemáticos de la biblioteca de **{$consoleName}**. La aventura sumerge al jugador en una trama absorbente repleta de desafíos épicos, personajes memorables y una atmósfera que aprovecha al máximo las capacidades del hardware original de la época.\n\n"
                . "A nivel jugable, destaca por su refinado sistema de control, mecánicas pulidas y un diseño de niveles que recompensa la exploración y la habilidad. Ya sea en sus secuencias de acción trepidante o en sus momentos de resolución estratégica, la experiencia mantiene un ritmo vibrante respaldado por una dirección artística excepcional y una banda sonora inolvidable que define la identidad de esta generación.\n\n"
                . "Hoy en día, este clásico se mantiene como una pieza de culto indispensable. Gracias a los estándares modernos de preservación digital y emulación de alta fidelidad con soporte para **60 FPS** y reescalado de texturas en alta resolución, los jugadores pueden redescubrir esta obra maestra en su máxima expresión visual, conservando fielmente la esencia que lo convirtió en leyenda.";
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
            . "{\n"
            . '  "release_year": 2005,' . "\n"
            . '  "developer": "Nombre del Desarrollador",' . "\n"
            . '  "publisher": "Nombre del Publicador",' . "\n"
            . '  "file_format": "ISO"' . "\n"
            . "}\n"
            . "Responde únicamente con el JSON puro.";

        $rawResponse = $this->callGeminiApi($prompt);

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
     * Prueba de conexión en vivo con Google Gemini API
     */
    public function testConnection(?string $apiKey = null, ?string $model = null): array
    {
        $apiKey = $apiKey ?: Setting::get('ai_api_key');
        $model = $model ?: Setting::get('ai_model', self::DEFAULT_MODEL);

        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'La clave API de Google Gemini no está configurada.',
            ];
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            $response = Http::withoutVerifying()->timeout(15)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Di exactamente: "Conexión exitosa con Google Gemini API para ROMHUB."']
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 60,
                ]
            ]);

            if ($response->successful()) {
                $reply = $response->json('candidates.0.content.parts.0.text') ?? 'Conexión OK';
                return [
                    'success' => true,
                    'model' => $model,
                    'message' => '¡Conexión exitosa con Google Gemini! Respuesta: ' . trim($reply),
                ];
            }

            $errorMsg = $response->json('error.message') ?? 'HTTP ' . $response->status();
            return [
                'success' => false,
                'message' => 'Error de Gemini API: ' . $errorMsg,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Excepción de red: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Genera automáticamente metadatos, palabras clave de búsqueda y diseño para una Saga de videojuegos
     */
    public function generateFranchiseData(string $franchiseName): array
    {
        $prompt = "Eres un historiador de videojuegos y diseñador UI para la plataforma de emulación ROMHUB.\n"
            . "Para la franquicia o saga de videojuegos '{$franchiseName}', genera un JSON válido con la siguiente estructura exacta:\n"
            . "{\n"
            . '  "subtitle": "Un subtítulo épico de 6 a 10 palabras sobre la saga",' . "\n"
            . '  "description": "Una breve reseña de 2 a 3 oraciones sobre el impacto y legado de la saga",' . "\n"
            . '  "color": "Color hex representativo de la saga (ej. #EAB308, #EF4444, #3B82F6, #10B981, #8B5CF6, #F97316)",' . "\n"
            . '  "icon": "Nombre de ícono Lucide representativo (ej. sword, shield, flame, sparkles, skull, crosshair, zap, gauge, trophy, gamepad-2, box, crown)",' . "\n"
            . '  "search_terms": "Lista de nombres alternativos, variantes ortográficas y subtítulos clave para encontrar todos sus juegos (separados por coma. Ejemplo: Final Fantasy, Crisis Core, Dirge of Cerberus)"' . "\n"
            . "}\n"
            . "Responde ÚNICAMENTE con el objeto JSON puro sin bloques markdown de código.";

        $rawResponse = $this->callGeminiApi($prompt);

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
     * Llamada oficial directa a Google Gemini API (v1beta)
     */
    protected function callGeminiApi(string $prompt): string
    {
        $apiKey = Setting::get('ai_api_key');
        $model = Setting::get('ai_model', self::DEFAULT_MODEL);
        $temperature = (float) Setting::get('ai_temperature', 0.7);

        if (empty($apiKey)) {
            return '';
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            $response = Http::withoutVerifying()
                ->connectTimeout(15)
                ->timeout(45)
                ->retry(2, 300)
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => $temperature,
                        'maxOutputTokens' => 2048,
                    ]
                ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text') ?? '';
            }
        } catch (Exception $e) {
            // Log or fallback
        }

        return '';
    }
}
