<?php

namespace App\Console\Commands;

use App\Models\Bios;
use App\Models\Setting;
use App\Services\StorageService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;


class SyncBiosToVaultCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vault:sync-bios-r2 
                            {--force : Forzar la descarga y resubida aunque el archivo ya exista}
                            {--id= : ID o lista de IDs separados por coma de BIOS específicas}
                            {--limit= : Número máximo de archivos a procesar}
                            {--dry-run : Simular la ejecución sin descargar ni transferir archivos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Descarga y transfiere paquetes de BIOS hacia la Bóveda Privada Cloud (R2) y actualiza los enlaces de descarga directa';

    /**
     * Execute the console command.
     */
    public function handle(StorageService $storageService): int
    {
        $this->info('🛡️  Verificando conexión con la Bóveda Privada de Almacenamiento...');

        $test = $storageService->testConnection();
        if (!$test['success']) {
            $this->error('❌ Error de conexión con la Bóveda de Almacenamiento: ' . $test['message']);
            return Command::FAILURE;
        }

        $this->line("✅ Bóveda verificada y operativa ({$test['latency_ms']}ms de latencia).");

        $query = Bios::query()->where('is_active', true);

        if ($ids = $this->option('id')) {
            $idArray = array_map('intval', explode(',', $ids));
            $query->whereIn('id', $idArray);
        }

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $items = $query->orderBy('order')->orderBy('id')->get();

        if ($items->isEmpty()) {
            $this->warn('No se encontraron registros de BIOS para procesar.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $this->info("🌐 Obteniendo catálogo en vivo de BIOS desde CDRomance...");
        $cdromancePacks = $this->fetchCdromanceCatalog();
        $this->line("   ✓ Se detectaron " . count($cdromancePacks) . " paquetes oficiales en CDRomance.");

        $this->newLine();
        $this->info("📦 Se procesarán {$items->count()} paquetes de BIOS para la Bóveda Privada...");
        $this->newLine();

        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $stats = [
            'uploaded' => 0,
            'skipped'  => 0,
            'failed'   => 0,
        ];

        // Obtener token dinámico de CDRomance
        $token = $this->getCdromanceToken();

        foreach ($items as $bios) {
            $this->line("▶ [ID #{$bios->id}] <comment>{$bios->system}</comment>");

            // Intentar encontrar el pack correspondiente en CDRomance
            $matchedCdr = $this->matchCdromancePack($bios->system, $cdromancePacks);

            $sourceUrl = null;
            $ext = 'zip';

            if ($matchedCdr && !empty($token)) {
                $this->line("   🔍 Coincidencia en CDRomance: <info>{$matchedCdr['title']}</info> ({$matchedCdr['filename']})");
                $sourceUrl = $this->resolveCdromanceDownloadUrl($matchedCdr, $token);
                $fileExt = pathinfo($matchedCdr['filename'], PATHINFO_EXTENSION);
                if (!empty($fileExt)) {
                    $ext = strtolower($fileExt);
                }
            }

            // Si no está en CDRomance o falló, usar download_url existente (ej. PS3 Firmware de Sony)
            if (empty($sourceUrl)) {
                $sourceUrl = $bios->download_url;
            }

            if (empty($sourceUrl)) {
                $this->warn("   ⚠️ Sin enlace de origen para descargar. Omitiendo.");
                $stats['skipped']++;
                continue;
            }

            if (str_contains(strtolower($sourceUrl), '.pup') || str_contains(strtolower($bios->format ?? ''), 'pup')) {
                $ext = 'pup';
            }

            $slugName = Str::slug($bios->system);
            $targetKey = "vault/bios/{$slugName}.{$ext}";

            // Verificar si ya existe en la bóveda privada
            $alreadyUploaded = $storageService->objectExists($targetKey);

            if ($alreadyUploaded && !$force) {
                $publicDomain = Setting::get('r2_public_url') ?: config('filesystems.disks.s3.url');
                $expectedUrl = rtrim($publicDomain, '/') . '/' . $targetKey;

                if ($bios->download_url !== $expectedUrl && !$dryRun) {
                    $bios->update(['download_url' => $expectedUrl]);
                    $this->info("   ⚡ Ya en bóveda. URL de descarga sincronizada a: {$expectedUrl}");
                } else {
                    $this->line("   ⏩ Ya existe en la bóveda privada. Omitiendo (usa --force para re-subir).");
                }

                $stats['skipped']++;
                continue;
            }

            if ($dryRun) {
                $this->info("   🔍 [DRY-RUN] Se descargaría de CDRomance ({$sourceUrl}) y se subiría a {$targetKey}");
                $stats['uploaded']++;
                continue;
            }

            try {
                $this->output->write("   ⬇️  Descargando desde CDRomance y transmitiendo a la Bóveda Privada... ");

                $result = $storageService->uploadFromUrl(
                    sourceUrl: $sourceUrl,
                    destinationKey: $targetKey,
                    contentType: $ext === 'pup' ? 'application/octet-stream' : ($ext === '7z' ? 'application/x-7z-compressed' : 'application/zip')
                );

                // Actualizar registro en BD con enlace directo CDN de la bóveda
                $bios->update([
                    'download_url' => $result['url'],
                    'size' => $result['size'],
                    'md5' => !empty($result['md5']) ? $result['md5'] : $bios->md5,
                    'sha1' => !empty($result['sha1']) ? $result['sha1'] : $bios->sha1,
                ]);

                $this->output->writeln("<info>✅ OK ({$result['size']})</info>");
                $this->line("      🔗 Nueva URL directa: <comment>{$result['url']}</comment>");
                $stats['uploaded']++;

            } catch (Exception $e) {
                $this->output->writeln("<error>❌ Error</error>");
                $this->error("      Detalle: " . $e->getMessage());
                $stats['failed']++;
            }
        }

        $this->newLine();
        $this->table(
            ['Métrica', 'Total'],
            [
                ['Procesados y Subidos', $stats['uploaded']],
                ['Omitidos (Ya en Bóveda)', $stats['skipped']],
                ['Errores de transferencia', $stats['failed']],
            ]
        );

        $this->info('✨ Proceso de sincronización a la Bóveda Privada finalizado.');
        return Command::SUCCESS;
    }

    /**
     * Extrae el catálogo completo de 38 BIOS desde CDRomance
     */
    protected function fetchCdromanceCatalog(): array
    {
        try {
            $html = @file_get_contents('https://cdromance.org/bios-files/', false, stream_context_create([
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                'http' => [
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'timeout' => 30,
                ],
            ]));

            if (!$html) {
                return [];
            }

            preg_match_all('#<tr[^>]*>.*?</tr>#is', $html, $rows);
            $packs = [];

            foreach ($rows[0] as $rowHtml) {
                if (!str_contains($rowHtml, 'dlc-button')) {
                    continue;
                }

                preg_match('#<td[^>]*class="[^"]*blacklink[^"]*"[^>]*>(.*?)</td>#is', $rowHtml, $tMatch);
                $title = trim(strip_tags($tMatch[1] ?? ''));

                preg_match('#<div[^>]*class="slidingDiv"[^>]*>(.*?)</div>#is', $rowHtml, $dMatch);
                $details = trim(strip_tags($dMatch[1] ?? ''));

                preg_match('#data-filename="([^"]+)"#i', $rowHtml, $fnMatch);
                preg_match('#data-server="([^"]+)"#i', $rowHtml, $srvMatch);
                preg_match('#data-id="([^"]+)"#i', $rowHtml, $idMatch);
                preg_match('#<td[^>]*class="[^"]*nowrap[^"]*"[^>]*>(.*?)</td>#is', $rowHtml, $szMatch);

                $packs[] = [
                    'title' => $title,
                    'filename' => $fnMatch[1] ?? '',
                    'server_id' => $srvMatch[1] ?? '6',
                    'post_id' => $idMatch[1] ?? '172982',
                    'size' => trim(strip_tags($szMatch[1] ?? '')),
                    'details' => $details,
                ];
            }

            return $packs;
        } catch (Exception) {
            return [];
        }
    }

    /**
     * Obtiene el token de seguridad dinámico de CDRomance
     */
    protected function getCdromanceToken(): ?string
    {
        try {
            $context = stream_context_create([
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                'http' => [
                    'header' => "Referer: https://cdromance.org/bios-files/\r\nUser-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Safari/537.36\r\n",
                    'timeout' => 15,
                ],
            ]);

            $nonce = @file_get_contents('https://cdromance.org/wp-admin/admin-ajax.php?action=cdr_nonce', false, $context);
            return $nonce ? trim($nonce) : null;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Obtiene el enlace final de descarga directa de un archivo en CDRomance
     */
    protected function resolveCdromanceDownloadUrl(array $pack, string $token): ?string
    {
        try {
            $postData = http_build_query([
                'server_id' => $pack['server_id'],
                'post_id'   => $pack['post_id'],
                'file_name' => $pack['filename'],
                'token'     => $token,
            ]);

            $context = stream_context_create([
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                                "Referer: https://cdromance.org/bios-files/\r\n" .
                                "X-Requested-With: XMLHttpRequest\r\n" .
                                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Safari/537.36\r\n",
                    'content' => $postData,
                    'timeout' => 20,
                ],
            ]);

            $response = @file_get_contents('https://cdromance.org/wp-content/plugins/cdr-main/public/dlc-page/direct.php', false, $context);
            if (!$response) {
                return null;
            }

            if (preg_match("#window\.location\s*=\s*'([^']+)'#i", $response, $matches)) {
                return $matches[1];
            }

            return null;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Busca la mejor coincidencia entre el nombre del sistema en la BD y el catálogo de CDRomance
     */
    protected function matchCdromancePack(string $system, array $cdrPacks): ?array
    {
        $normalizedSystem = strtolower($system);

        // Mapeos directos prioritarios
        $priorityMap = [
            'playstation 2' => 'ps2 bios complete',
            'playstation 1' => 'psx psone bios pack',
            'dreamcast' => 'dreamcast bios pack',
            'saturn cd block' => 'saturn cd block rom',
            'saturn bios' => 'sega saturn bios',
            'action replay' => 'sega saturn action replay',
            'gameboy' => 'gameboy bios',
            'game boy' => 'gameboy bios',
            'gamecube' => 'gamecube (gcn)',
            'nintendo 64' => 'nintendo 64dd',
            'nintendo ds' => 'nds bios and firmware',
            '3do' => '3do bios',
            'amiga' => 'amiga bios',
            'atari' => 'atari bios',
            'cd-i' => 'cd-i (philips)',
            'colecovision' => 'colecovision',
            'famicom' => 'famicom disk system',
            'intellivision' => 'intellivision',
            'mac quadra' => 'mac quadra',
            'mame' => 'mame 0.133',
            'mess' => 'mess (complete',
            'msx' => 'microsoft msx',
            'xbox' => 'microsoft xbox bios',
            'neo-geo cd' => 'neo-geo cd bios',
            'neo-geo' => 'neo-geo bios',
            'pc-fx' => 'pc-fx bios',
            'pc engine' => 'pc engine',
            'satellaview' => 'satellaview',
            'sega 32x' => 'sega 32x bios',
            'sega cd' => 'sega cd bios',
            'megadrive' => 'sega genesis - megadrive',
            'master system' => 'sega master system',
            'snes' => 'snes bios',
            'st-v' => 'st-v bios',
            'xband' => 'xband modem',
            'gamars' => 'gamars',
        ];

        foreach ($priorityMap as $key => $targetCdrTitle) {
            if (str_contains($normalizedSystem, $key)) {
                foreach ($cdrPacks as $pack) {
                    if (str_contains(strtolower($pack['title']), $targetCdrTitle)) {
                        return $pack;
                    }
                }
            }
        }

        // Búsqueda difusa por palabras
        $bestMatch = null;
        $highestSimilarity = 0;

        foreach ($cdrPacks as $pack) {
            similar_text(strtolower($pack['title']), $normalizedSystem, $percent);
            if ($percent > $highestSimilarity && $percent > 40) {
                $highestSimilarity = $percent;
                $bestMatch = $pack;
            }
        }

        return $bestMatch;
    }
}

