<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    /**
     * Get the Telegram Bot Token.
     */
    public function getBotToken(?string $override = null): string
    {
        return trim($override ?: (string) Setting::get('telegram_bot_token', ''));
    }

    /**
     * Get the Telegram Channel/Chat ID (e.g. @micanal or -100123456789).
     */
    public function getChannelId(?string $override = null): string
    {
        return trim($override ?: (string) Setting::get('telegram_channel_id', ''));
    }

    /**
     * Test the Telegram Bot token and optionally send a test message to the channel.
     *
     * @return array{success: bool, message: string, bot_username: ?string, error: ?string}
     */
    public function testConnection(?string $token = null, ?string $channelId = null): array
    {
        $botToken = $this->getBotToken($token);
        $chatId = $this->getChannelId($channelId);

        if (empty($botToken)) {
            return [
                'success' => false,
                'message' => 'No se ha configurado el Token del Bot de Telegram.',
                'bot_username' => null,
                'error' => 'Token vacío.',
            ];
        }

        try {
            // 1. Verify Bot Token with getMe
            $response = Http::withoutVerifying()->timeout(10)->get("https://api.telegram.org/bot{$botToken}/getMe");

            if (! $response->successful()) {
                $err = $response->json('description') ?? 'Token de bot inválido o no reconocido por Telegram.';

                return [
                    'success' => false,
                    'message' => 'Fallo al autenticar el Bot de Telegram.',
                    'bot_username' => null,
                    'error' => $err,
                ];
            }

            $botData = $response->json('result') ?? [];
            $botUsername = $botData['username'] ?? 'Bot';

            // 2. If channel ID provided, test sending a message to verify admin permissions
            if (! empty($chatId)) {
                $siteName = Setting::get('site_name', 'ROM Preservation Vault');
                $testText = "🤖 <b>¡Conexión Exitosa con {$siteName}!</b>\n\n"
                    ."El Bot oficial <code>@{$botUsername}</code> está correctamente conectado y listo para publicar novedades de ROMs en este canal.";

                $sendResponse = Http::withoutVerifying()->timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $testText,
                    'parse_mode' => 'HTML',
                ]);

                if (! $sendResponse->successful()) {
                    $sendErr = $sendResponse->json('description') ?? 'Error al enviar mensaje al canal.';

                    return [
                        'success' => false,
                        'message' => "El bot @{$botUsername} es válido, pero falló al escribir en el canal {$chatId}.",
                        'bot_username' => $botUsername,
                        'error' => "Telegram dice: {$sendErr}. Asegúrate de que el bot sea ADMINISTRADOR del canal con permisos de publicar mensajes.",
                    ];
                }

                return [
                    'success' => true,
                    'message' => "¡Conexión exitosa! El bot @{$botUsername} envió un mensaje de prueba al canal {$chatId}.",
                    'bot_username' => $botUsername,
                    'error' => null,
                ];
            }

            return [
                'success' => true,
                'message' => "¡Token válido! El bot @{$botUsername} está activo. Recuerda agregarlo como administrador a tu canal para poder publicar.",
                'bot_username' => $botUsername,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('Telegram connection test error: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Error de conexión con los servidores de Telegram.',
                'bot_username' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Publish a game to the Telegram channel.
     *
     * @return array{success: bool, message: string, message_id: ?int, error: ?string}
     */
    public function publishGame(Game $game, bool $force = false): array
    {
        if (! $force && $game->telegram_sent_at !== null) {
            return [
                'success' => false,
                'message' => 'Este juego ya fue publicado en Telegram previamente.',
                'message_id' => null,
                'error' => 'Ya publicado.',
            ];
        }

        $botToken = $this->getBotToken();
        $chatId = $this->getChannelId();

        if (empty($botToken) || empty($chatId)) {
            return [
                'success' => false,
                'message' => 'Faltan credenciales de Telegram (Token o ID del Canal).',
                'message_id' => null,
                'error' => 'Configuración incompleta.',
            ];
        }

        try {
            $gameUrl = route('game.show', $game->slug);
            $consoleName = $game->console ? $game->console->name : 'Retro Console';
            $caption = $this->buildMessageText($game, $gameUrl, $consoleName);

            $inlineKeyboard = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => '🎮 DESCARGAR ROM AHORA',
                            'url' => $gameUrl,
                        ],
                    ],
                ],
            ];

            $photoUrl = $game->cover_thumb_url ?: $game->cover_url;

            // Attempt 1: Send Photo with Caption if image URL is available
            if (! empty($photoUrl) && filter_var($photoUrl, FILTER_VALIDATE_URL)) {
                $photoResponse = Http::withoutVerifying()->timeout(15)->post("https://api.telegram.org/bot{$botToken}/sendPhoto", [
                    'chat_id' => $chatId,
                    'photo' => $photoUrl,
                    'caption' => $caption,
                    'parse_mode' => 'HTML',
                    'reply_markup' => json_encode($inlineKeyboard),
                ]);

                if ($photoResponse->successful()) {
                    $msgId = $photoResponse->json('result.message_id');
                    $game->update(['telegram_sent_at' => now()]);

                    return [
                        'success' => true,
                        'message' => '¡Juego publicado exitosamente con carátula en Telegram!',
                        'message_id' => $msgId,
                        'error' => null,
                    ];
                }

                Log::warning("Telegram sendPhoto failed for game #{$game->id}, fallback to sendMessage: ".$photoResponse->body());
            }

            // Attempt 2: Fallback to text message
            $msgResponse = Http::withoutVerifying()->timeout(12)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $caption,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => false,
                'reply_markup' => json_encode($inlineKeyboard),
            ]);

            if ($msgResponse->successful()) {
                $msgId = $msgResponse->json('result.message_id');
                $game->update(['telegram_sent_at' => now()]);

                return [
                    'success' => true,
                    'message' => '¡Juego publicado exitosamente en Telegram!',
                    'message_id' => $msgId,
                    'error' => null,
                ];
            }

            $errMsg = $msgResponse->json('description') ?? 'Fallo al publicar mensaje.';

            return [
                'success' => false,
                'message' => 'Telegram rechazó el envío de la publicación.',
                'message_id' => null,
                'error' => $errMsg,
            ];
        } catch (\Throwable $e) {
            Log::error("Error publishing game #{$game->id} to Telegram: ".$e->getMessage());

            return [
                'success' => false,
                'message' => 'Excepción de red al contactar a Telegram.',
                'message_id' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build the message text using custom template or high-converting default.
     */
    protected function buildMessageText(Game $game, string $gameUrl, string $consoleName): string
    {
        $customTemplate = Setting::get('telegram_message_template', '');

        $title = htmlspecialchars($game->title, ENT_QUOTES, 'UTF-8');
        $console = htmlspecialchars($consoleName, ENT_QUOTES, 'UTF-8');
        $region = htmlspecialchars($game->region ?: 'Universal / Libre', ENT_QUOTES, 'UTF-8');
        $size = htmlspecialchars($game->formatted_size ?: 'N/A', ENT_QUOTES, 'UTF-8');
        $year = $game->release_year ?: 'Retro Clásico';
        $developer = htmlspecialchars($game->developer ?: 'Archivo Vault', ENT_QUOTES, 'UTF-8');

        if (! empty(trim($customTemplate))) {
            $search = ['{title}', '{console}', '{region}', '{size}', '{year}', '{url}', '{developer}'];
            $replace = [$title, $console, $region, $size, $year, $gameUrl, $developer];

            return str_replace($search, $replace, $customTemplate);
        }

        // High-converting retro template with emojis
        return "🕹️ <b>¡NUEVA ROM PUBLICADA EN EL VAULT!</b>\n\n"
            ."🎮 <b>Título:</b> {$title}\n"
            ."💾 <b>Consola:</b> {$console}\n"
            ."🌍 <b>Región:</b> {$region}\n"
            ."📦 <b>Tamaño:</b> {$size}\n"
            ."📅 <b>Año:</b> {$year}\n\n"
            ."⚡ <i>Archivo verificado, limpio y con descarga directa de alta velocidad.</i>\n\n"
            ."🔗 <b>Ficha & Enlaces:</b> {$gameUrl}";
    }
}
