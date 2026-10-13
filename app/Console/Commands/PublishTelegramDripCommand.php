<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Setting;
use App\Services\TelegramBotService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishTelegramDripCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:publish-drip
                            {--limit=1 : Cantidad de juegos a despachar en esta tanda (por defecto 1)}
                            {--force : Despachar ignorando si el piloto automático de Telegram está pausado}
                            {--dry-run : Simular el envío sin contactar a la API de Telegram}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Despacha juegos publicados a Telegram de forma dosificada (Drip-Feed) para evitar saturar el canal';

    /**
     * Execute the console command.
     */
    public function handle(TelegramBotService $telegramBot): int
    {
        $autoPublish = Setting::get('telegram_auto_publish', '0') === '1';
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');
        $limit = max(1, (int) $this->option('limit'));

        if (! $autoPublish && ! $force) {
            $this->line('⏸️ Despacho automático de Telegram pausado (telegram_auto_publish=0). Usa --force para ejecutar manualmente.');

            return self::SUCCESS;
        }

        $botToken = $telegramBot->getBotToken();
        $channelId = $telegramBot->getChannelId();

        if (empty($botToken) || empty($channelId)) {
            $this->warn('⚠️ Faltan credenciales de Telegram (Token o ID del Canal no configurados).');
            Log::channel('single')->warning('[Telegram-Drip] Faltan credenciales de Telegram configuradas.');

            return self::SUCCESS;
        }

        // Buscar juegos en estado PUBLISHED que aún no hayan sido enviados a Telegram
        $games = Game::with('console')
            ->where('status', 'PUBLISHED')
            ->whereNull('telegram_sent_at')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($games->isEmpty()) {
            $this->line('ℹ️ No hay juegos publicados pendientes por enviar a Telegram.');

            return self::SUCCESS;
        }

        $this->info("🚀 Despachando {$games->count()} juego(s) a Telegram...");

        $sentCount = 0;
        $failedCount = 0;
        $dispatchedTitles = [];

        foreach ($games as $game) {
            $consoleName = $game->console ? $game->console->name : 'Retro Console';

            $this->line("⏳ Procesando: <info>{$game->title}</info> ({$consoleName})");

            if ($dryRun) {
                $this->comment("   [DRY-RUN] Se habría publicado en Telegram: {$game->title} (ID #{$game->id})");
                $sentCount++;
                $dispatchedTitles[] = "{$game->title} [SIMULADO]";

                continue;
            }

            $result = $telegramBot->publishGame($game);

            if ($result['success']) {
                $sentCount++;
                $dispatchedTitles[] = "{$game->title} (#{$game->id})";
                $this->info("   ✅ Publicado exitosamente en Telegram (Msg ID: {$result['message_id']})");
            } else {
                $failedCount++;
                $this->error("   ❌ Falló publicación: {$result['message']}".($result['error'] ? " ({$result['error']})" : ''));

                // Si Telegram arrojó error de límite de velocidad (429) o bloqueo temporal, abortamos el lote actual
                if (str_contains($result['error'] ?? '', '429') || str_contains($result['error'] ?? '', 'Too Many Requests')) {
                    $this->warn('⚠️ Telegram reportó límite de tasa (Rate Limit 429). Deteniendo tanda actual para evitar penalizaciones.');
                    break;
                }
            }
        }

        $remainingCount = Game::where('status', 'PUBLISHED')->whereNull('telegram_sent_at')->count();

        // Registrar en log dedicado
        $logMessage = sprintf(
            '[Telegram-Drip] %s Enviados: %d, Fallidos: %d. Títulos: [%s]. Restantes en cola: %d',
            $dryRun ? '[SIMULACIÓN]' : '[REAL]',
            $sentCount,
            $failedCount,
            implode(', ', $dispatchedTitles),
            $remainingCount
        );

        $logPath = storage_path('logs/telegram-publisher.log');
        @file_put_contents($logPath, '['.now()->toDateTimeString().'] '.$logMessage.PHP_EOL, FILE_APPEND);

        $this->info("📊 Resumen: {$sentCount} enviado(s), {$remainingCount} pendiente(s) en cola.");

        return self::SUCCESS;
    }
}
