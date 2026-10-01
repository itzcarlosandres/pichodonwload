@extends('layouts.admin')

@section('title', 'Explorador de Catálogo & Importador 1-Clic — ROMHUB')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12" x-data="scraperCatalogApp()" x-init="loadCatalog()">

    <!-- Top Breadcrumb & Luxury Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#1E2536] pb-5">
        <div>
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs font-mono text-gray-500 mb-2">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-300 transition-colors">Admin</a>
                <i data-lucide="chevron-right" class="w-3 h-3 text-gray-600"></i>
                <span class="text-purple-400 font-semibold">Laboratorio</span>
                <i data-lucide="chevron-right" class="w-3 h-3 text-gray-600"></i>
                <span class="text-gray-300">Catálogo 1-Clic</span>
            </nav>

            <!-- Main Heading -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-purple-500/25 via-[#161B28] to-indigo-500/15 border border-purple-500/30 flex items-center justify-center shadow-lg shadow-purple-500/10">
                    <i data-lucide="layers" class="w-5 h-5 text-purple-400"></i>
                </div>
                <div>
                    <h1 class="text-2xl lg:text-3xl font-black text-white tracking-tight font-heading flex items-center gap-2.5">
                        <span>Catálogo en 1 Clic</span>
                        <span class="text-[10px] uppercase font-mono tracking-widest font-extrabold px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                            Ultra Rápido
                        </span>
                    </h1>
                    <p class="text-xs text-gray-400 font-sans mt-0.5">Explora catálogos en vivo, descarta duplicados y publica con IA en segundos.</p>
                </div>
            </div>
        </div>

        <!-- Mode Navigation Tabs & Safety Badge -->
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <div class="hidden sm:inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-[#0B0E14] border border-[#1E2536] text-xs font-mono shadow-sm">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-gray-400">Anti-Bloqueo:</span>
                <span class="font-bold text-emerald-400">Activo</span>
            </div>

            <!-- Segmented Mode Switcher -->
            <div class="inline-flex p-1 bg-[#090C12] border border-[#1E2536] rounded-2xl shadow-inner">
                <a href="{{ route('admin.scraper.catalog') }}" 
                   class="px-4 py-2 rounded-xl text-xs font-mono font-bold bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-md shadow-purple-600/25 flex items-center gap-1.5 transition-all">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    <span>Catálogo 1-Clic</span>
                </a>
                <a href="{{ route('admin.scraper.demo') }}" 
                   class="px-4 py-2 rounded-xl text-xs font-mono font-bold text-gray-400 hover:text-white hover:bg-[#141824] flex items-center gap-1.5 transition-colors">
                    <i data-lucide="zap" class="w-3.5 h-3.5 text-purple-400"></i>
                    <span>Extractor por URL</span>
                </a>
            </div>
        </div>
    </div>
 
    <!-- Autopilot Executive Control Hub (HUD Panel) -->
    <div class="relative rounded-3xl p-6 bg-gradient-to-br from-[#121622] via-[#0E121A] to-[#141926] border border-purple-500/20 shadow-2xl overflow-hidden">
        <!-- Ambient Blur Glows -->
        <div class="absolute -right-16 -top-16 w-56 h-56 bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-56 h-56 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <!-- Left Info Section -->
            <div class="space-y-3 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Status Pulse Pill Dynamic -->
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-mono font-bold transition-colors"
                          :class="autopilotSettings.autopilot_enabled ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border border-amber-500/30'">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                                  :class="autopilotSettings.autopilot_enabled ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2"
                                  :class="autopilotSettings.autopilot_enabled ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                        </span>
                        <span x-text="autopilotSettings.autopilot_enabled ? 'Piloto Automático Activo' : 'Piloto Automático en Pausa'"></span>
                    </span>

                    <!-- Cadence Chip Dynamic -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono bg-[#141924] border border-[#232B3E] text-gray-300">
                        <i data-lucide="clock" class="w-3 h-3 text-purple-400"></i>
                        <span>Publicando <strong class="text-white font-bold" x-text="autopilotSettings.posts_per_batch + ' juegos'"></strong> / <strong class="text-purple-300 font-bold" x-text="autopilotSettings.batch_interval_hours + 'h'"></strong></span>
                    </span>

                    <!-- AI Mode Chip -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono bg-purple-500/10 border border-purple-500/25 text-purple-300">
                        <i data-lucide="sparkles" class="w-3 h-3 text-purple-400"></i>
                        <span x-text="autopilotSettings.auto_ai_enrich ? 'Gemini IA Redactando' : 'Sin IA'"></span>
                    </span>

                    <!-- Google Safe Chip -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono bg-blue-500/10 border border-blue-500/25 text-blue-300">
                        <i data-lucide="shield-check" class="w-3 h-3 text-blue-400"></i>
                        <span>Anti-Duplicados Estricto</span>
                    </span>
                </div>

                <!-- Explanation Text -->
                <p class="text-xs text-gray-300 leading-relaxed font-sans">
                    Los juegos explorados desde <strong class="text-red-400 font-bold">Romsemu</strong>, <strong class="text-blue-400 font-bold">CDRomance</strong> y <strong class="text-purple-400 font-bold">Romspedia</strong> se verifican contra duplicados y se almacenan en cola <code class="text-amber-300 bg-amber-950/40 px-1.5 py-0.5 rounded border border-amber-500/30">DRAFT</code>. El cron automático los publica progresivamente con sinopsis enriquecidas por IA para generar tráfico orgánico natural.
                </p>
            </div>

            <!-- Right Actions & Counters -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 shrink-0">
                <!-- Draft Queue Widget -->
                <div class="px-4 py-2.5 bg-[#080B10]/80 border border-[#1E2536] rounded-2xl text-center min-w-[105px] shadow-inner">
                    <div class="text-[10px] uppercase font-mono tracking-wider text-gray-500 font-bold">En Cola DRAFT</div>
                    <div class="text-2xl font-black text-amber-400 font-mono tracking-tight" x-text="autopilotQueueCount">
                        {{ \App\Models\Game::where('status', 'DRAFT')->count() }}
                    </div>
                    <div class="text-[9px] text-gray-400 font-mono">Listos para goteo</div>
                </div>

                <!-- Botón 1: Cargar a Cola DRAFT (Auto-Alimentación) -->
                <button type="button"
                        @click="openHarvestModal = true"
                        class="px-4 py-3 rounded-2xl text-xs font-mono font-bold bg-gradient-to-r from-amber-600 via-amber-500 to-orange-600 hover:from-amber-500 hover:to-orange-500 active:scale-95 text-white shadow-xl shadow-amber-600/25 flex items-center gap-2 transition-all cursor-pointer">
                    <i data-lucide="download-cloud" class="w-4 h-4"></i>
                    <span>Cargar a Cola DRAFT</span>
                </button>

                <!-- Botón 2: Publicar X Ahora (Dinámico con posts_per_batch) -->
                <button type="button"
                        @click="triggerDripNow()"
                        :disabled="dripLoading"
                        class="px-4 py-3 rounded-2xl text-xs font-mono font-bold bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-600 hover:from-purple-500 hover:to-indigo-500 active:scale-95 text-white shadow-xl shadow-purple-600/30 flex items-center gap-2 transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <i data-lucide="send" class="w-4 h-4" :class="{'animate-spin': dripLoading}"></i>
                    <span x-text="dripLoading ? 'Publicando...' : `Publicar ${autopilotSettings.posts_per_batch} Ahora`"></span>
                </button>

                <!-- Botón 3: Ajustes Piloto Automático -->
                <button type="button"
                        @click="openSettingsModal = true"
                        class="p-3 rounded-2xl bg-[#080B10] hover:bg-[#141926] border border-[#1E2536] hover:border-purple-500/50 text-gray-300 hover:text-white transition-all cursor-pointer shadow-md"
                        title="Configurar Piloto Automático y cadencia">
                    <i data-lucide="settings" class="w-4 h-4 text-purple-400"></i>
                </button>
            </div>
        </div>

        <!-- Alert Notification for Drip -->
        <div x-show="dripNotice" x-transition class="mt-4 p-3.5 rounded-2xl text-xs font-mono flex items-center justify-between gap-3 border shadow-lg"
             :class="dripNoticeSuccess ? 'bg-emerald-950/80 border-emerald-500/40 text-emerald-300' : 'bg-amber-950/80 border-amber-500/40 text-amber-300'">
            <div class="flex items-center gap-2.5">
                <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                <span x-text="dripNotice" class="font-bold"></span>
            </div>
            <button type="button" @click="dripNotice = ''" class="text-gray-400 hover:text-white px-2 py-0.5 rounded cursor-pointer font-bold">&times;</button>
        </div>
    </div>

    <!-- Command Center Bar (Organized Filter & Tools Dock) -->
    <div class="rounded-3xl p-5 bg-[#0D111A] border border-[#1E2536] shadow-2xl space-y-4">
        
        <!-- Tier 1: Fuente (Provider) & Selector de Consola -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            <!-- Left: Provider Tabs Switcher -->
            <div class="inline-flex items-center p-1.5 rounded-2xl bg-[#07090F] border border-[#1E2536] shadow-inner gap-1.5 shrink-0 max-w-full overflow-x-auto no-scrollbar">
                <!-- Romsemu Button -->
                <button type="button" 
                        @click="setProvider('romsemu')" 
                        class="px-3.5 sm:px-4 py-2 rounded-xl text-xs font-mono font-bold transition-all flex items-center gap-2 cursor-pointer shrink-0 whitespace-nowrap"
                        :class="provider === 'romsemu' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30 border border-rose-400/40' : 'text-gray-400 hover:text-white hover:bg-[#141824]'">
                    <i data-lucide="cpu" class="w-3.5 h-3.5"></i>
                    <span>Romsemu</span>
                    <span class="w-1.5 h-1.5 rounded-full" :class="provider === 'romsemu' ? 'bg-white' : 'bg-transparent'"></span>
                </button>

                <!-- CDRomance Button -->
                <button type="button" 
                        @click="setProvider('cdromance')" 
                        class="px-3.5 sm:px-4 py-2 rounded-xl text-xs font-mono font-bold transition-all flex items-center gap-2 cursor-pointer shrink-0 whitespace-nowrap"
                        :class="provider === 'cdromance' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 border border-blue-400/40' : 'text-gray-400 hover:text-white hover:bg-[#141824]'">
                    <i data-lucide="disc-3" class="w-3.5 h-3.5"></i>
                    <span>CDRomance</span>
                    <span class="w-1.5 h-1.5 rounded-full" :class="provider === 'cdromance' ? 'bg-white' : 'bg-transparent'"></span>
                </button>

                <!-- Romspedia Button -->
                <button type="button" 
                        @click="setProvider('romspedia')" 
                        class="px-3.5 sm:px-4 py-2 rounded-xl text-xs font-mono font-bold transition-all flex items-center gap-2 cursor-pointer shrink-0 whitespace-nowrap"
                        :class="provider === 'romspedia' ? 'bg-purple-600 text-white shadow-lg shadow-purple-600/30 border border-purple-400/40' : 'text-gray-400 hover:text-white hover:bg-[#141824]'">
                    <i data-lucide="gamepad-2" class="w-3.5 h-3.5"></i>
                    <span>Romspedia</span>
                    <span class="w-1.5 h-1.5 rounded-full" :class="provider === 'romspedia' ? 'bg-white' : 'bg-transparent'"></span>
                </button>
            </div>

            <!-- Right: Console Dropdown & Refresh Button Unified -->
            <div class="flex items-center gap-2.5 flex-1 lg:max-w-md w-full">
                <!-- Styled Console Dropdown -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-500">
                        <i data-lucide="tv" class="w-4 h-4"></i>
                    </div>
                    <select x-model="consoleSlug" 
                            @change="page = 1; loadCatalog()"
                            class="w-full bg-[#07090F] border border-[#1E2536] rounded-2xl pl-10 pr-9 py-2.5 text-xs text-white focus:outline-none focus:border-purple-500 font-mono transition-all appearance-none cursor-pointer shadow-inner">
                        <template x-for="item in currentConsoles" :key="item.slug">
                            <option :value="item.slug" x-text="item.name" :selected="item.slug === consoleSlug" style="background-color: #0E121A; color: #FFFFFF;"></option>
                        </template>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-500">
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </div>
                </div>

                <!-- Refresh Button Locked Beside Dropdown -->
                <button type="button" 
                        @click="loadCatalog()" 
                        :disabled="loading"
                        class="p-2.5 rounded-2xl bg-[#07090F] hover:bg-[#141824] border border-[#1E2536] hover:border-gray-600 text-gray-300 hover:text-white transition-all cursor-pointer disabled:opacity-50 shrink-0 shadow-sm"
                        title="Refrescar catálogo">
                    <i data-lucide="refresh-cw" class="w-4 h-4" :class="loading ? 'animate-spin text-purple-400' : ''"></i>
                </button>
            </div>

        </div>

        <!-- Tier 2: Barra de Herramientas de Importación Rápida -->
        <div class="pt-3.5 border-t border-[#191F2D] flex flex-wrap items-center justify-between gap-3 text-xs font-mono">
            <div class="flex items-center gap-2 text-gray-400">
                <i data-lucide="sliders" class="w-3.5 h-3.5 text-purple-400"></i>
                <span class="font-bold text-gray-300">Ajustes de 1-Clic:</span>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Toggle Piloto Automático IA -->
                <label class="flex items-center gap-2 cursor-pointer px-3.5 py-1.5 rounded-2xl border transition-all select-none shadow-sm"
                       :class="generateAi ? 'bg-purple-950/40 border-purple-500/50 text-purple-200 ring-1 ring-purple-500/30' : 'bg-[#07090F] border-[#1E2536] text-gray-400 hover:text-gray-200'"
                       title="Redacta sinopsis enriquecida (~200 palabras) y tags SEO con Gemini IA">
                    <input type="checkbox" x-model="generateAi" class="hidden">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5" :class="generateAi ? 'text-purple-400' : 'text-gray-500'"></i>
                    <span class="font-bold">Piloto Automático IA</span>
                    <span class="w-2 h-2 rounded-full transition-colors" :class="generateAi ? 'bg-purple-400 shadow-sm shadow-purple-400' : 'bg-gray-600'"></span>
                </label>

                <!-- Estado Selector Pill -->
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-2xl bg-[#07090F] border border-[#1E2536]">
                    <i data-lucide="tag" class="w-3.5 h-3.5 text-gray-500"></i>
                    <span class="text-gray-400 font-bold">Estado:</span>
                    <select x-model="importStatus" class="bg-transparent text-white font-mono text-xs focus:outline-none cursor-pointer">
                        <option value="DRAFT" style="background-color: #0E121A;">Borrador (DRAFT)</option>
                        <option value="PUBLISHED" style="background-color: #0E121A;">Publicado (PUBLISHED)</option>
                    </select>
                </div>

                <!-- WebP Convert Checkbox Pill -->
                <label class="flex items-center gap-2 cursor-pointer px-3.5 py-1.5 rounded-2xl border transition-all select-none shadow-sm"
                       :class="optimizeCover ? 'bg-emerald-950/40 border-emerald-500/50 text-emerald-200 ring-1 ring-emerald-500/30' : 'bg-[#07090F] border-[#1E2536] text-gray-400 hover:text-gray-200'"
                       title="Convierte la carátula local a formato WebP optimizado">
                    <input type="checkbox" x-model="optimizeCover" class="hidden">
                    <i data-lucide="image" class="w-3.5 h-3.5" :class="optimizeCover ? 'text-emerald-400' : 'text-gray-500'"></i>
                    <span>WebP Local</span>
                    <span class="w-2 h-2 rounded-full transition-colors" :class="optimizeCover ? 'bg-emerald-400 shadow-sm shadow-emerald-400' : 'bg-gray-600'"></span>
                </label>
            </div>
        </div>

    </div>

    <!-- Feedback Message Banner -->
    <div x-show="feedbackMessage" x-cloak class="p-4 rounded-2xl border text-xs flex items-center justify-between gap-3 shadow-lg"
         :class="feedbackSuccess ? 'bg-emerald-950/80 border-emerald-500/40 text-emerald-300' : 'bg-rose-950/80 border-rose-500/40 text-rose-300'">
        <div class="flex items-center gap-2.5">
            <i :data-lucide="feedbackSuccess ? 'check-circle-2' : 'alert-circle'" class="w-4 h-4 shrink-0"></i>
            <span x-text="feedbackMessage" class="font-medium"></span>
        </div>
        <div class="flex items-center gap-2">
            <template x-if="!feedbackSuccess && provider === 'romspedia'">
                <button type="button" @click="setProvider('romsemu')" class="px-3 py-1 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-mono text-[11px] font-bold cursor-pointer shrink-0">
                    Cambiar a Romsemu
                </button>
            </template>
            <template x-if="lastImportedEditUrl">
                <a :href="lastImportedEditUrl" target="_blank" rel="noopener noreferrer" class="font-bold underline text-white hover:text-emerald-200 shrink-0 flex items-center gap-1 font-mono">
                    <span>Editar Ficha</span>
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                </a>
            </template>
        </div>
    </div>

    <!-- Cooldown / Anti-Ban Safety Alert Banner -->
    <div x-show="cooldownActive" x-cloak class="p-4 rounded-2xl border border-amber-500/40 bg-amber-950/70 text-amber-200 text-xs flex flex-wrap items-center justify-between gap-3 shadow-lg">
        <div class="flex items-center gap-3">
            <i data-lucide="shield-alert" class="w-5 h-5 text-amber-400 shrink-0"></i>
            <div>
                <div class="font-bold text-amber-300">Pausa de Seguridad Preventiva Activada</div>
                <div class="text-[11px] text-amber-200/90" x-text="cooldownMessage"></div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="font-mono text-amber-400 font-bold px-2.5 py-1 rounded bg-black/40 border border-amber-500/30 text-[11px]" x-text="'Reanudando en: ' + cooldownSeconds + 's'"></span>
            <button type="button" @click="resetCooldown()" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-black font-bold text-xs font-mono transition-colors cursor-pointer shadow-sm">
                Reanudar Ahora
            </button>
        </div>
    </div>

    <!-- Status / Context & Provider Mode Ribbon -->
    <div class="flex flex-wrap items-center justify-between gap-3 px-1 py-1 text-xs font-mono">
        <div class="flex items-center gap-2.5">
            <!-- Romsemu Ribbon -->
            <template x-if="provider === 'romsemu'">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-rose-950/40 border border-rose-500/30 text-rose-300">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    <strong class="text-white">Romsemu:</strong>
                    <span x-show="consoleSlug === 'all'" class="text-rose-300 font-bold">⭐ Últimos Agregados (Todos los Sistemas en Vivo)</span>
                    <span x-show="consoleSlug !== 'all'">🎮 Catálogo Oficial de <strong class="text-white" x-text="getConsoleName(consoleSlug)"></strong></span>
                </div>
            </template>

            <!-- CDRomance Ribbon -->
            <template x-if="provider === 'cdromance'">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-blue-950/40 border border-blue-500/30 text-blue-300">
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                    <strong class="text-white">CDRomance:</strong>
                    <span x-show="consoleSlug === 'all'" class="text-blue-300 font-bold">⭐ Últimos Agregados Recientes (Todos los Sistemas)</span>
                    <span x-show="consoleSlug !== 'all'">⭐ Novedades de <strong class="text-white" x-text="getConsoleName(consoleSlug)"></strong></span>
                </div>
            </template>

            <!-- Romspedia Ribbon -->
            <template x-if="provider === 'romspedia'">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-purple-950/40 border border-purple-500/30 text-purple-300">
                    <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></span>
                    <strong class="text-white">Romspedia:</strong>
                    <span>🏆 Top Populares & Más Descargados (<strong class="text-white" x-text="getConsoleName(consoleSlug)"></strong>)</span>
                </div>
            </template>
        </div>

        <div class="flex items-center gap-3 text-gray-400 text-[11px]" x-show="!loading && games.length > 0">
            <span class="px-2.5 py-1 rounded-xl bg-[#090C12] border border-[#1E2536] text-gray-300">
                <strong class="text-white font-bold" x-text="games.length"></strong> juegos en página
            </span>
            <span class="hidden sm:inline-flex items-center gap-1 text-emerald-400">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                <span>Anti-Duplicados</span>
            </span>
        </div>
    </div>

    <!-- Games Grid & Content -->
    <div class="space-y-6">
        
        <!-- Loading Skeletons -->
        <div x-show="loading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3.5">
            <template x-for="i in 12" :key="i">
                <div class="bg-[#0D111A] border border-[#1E2536] rounded-2xl p-3 space-y-3 animate-pulse">
                    <div class="aspect-[3/4] bg-[#141926] rounded-xl w-full"></div>
                    <div class="h-3.5 bg-[#141926] rounded w-3/4"></div>
                    <div class="h-8 bg-[#141926] rounded-xl w-full"></div>
                </div>
            </template>
        </div>

        <!-- Games Cards List -->
        <div x-show="!loading && games.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3.5">
            <template x-for="(game, idx) in games" :key="game.url">
                <div class="rounded-2xl p-3 flex flex-col justify-between transition-all duration-300 group relative overflow-hidden shadow-lg"
                     :class="game.already_imported 
                        ? 'bg-[#091211] border border-emerald-500/40 shadow-emerald-950/20 ring-1 ring-emerald-500/20' 
                        : (provider === 'romsemu' 
                            ? 'bg-[#0D111A] border border-[#1E2536] hover:border-rose-500/50 hover:shadow-rose-950/30 hover:-translate-y-1' 
                            : (provider === 'cdromance'
                                ? 'bg-[#0D111A] border border-[#1E2536] hover:border-blue-500/50 hover:shadow-blue-950/30 hover:-translate-y-1'
                                : 'bg-[#0D111A] border border-[#1E2536] hover:border-purple-500/50 hover:shadow-purple-950/30 hover:-translate-y-1'))">
                    
                    <!-- Top: Cover & Thumbnail -->
                    <div class="space-y-2.5">
                        <div class="aspect-[3/4] w-full rounded-xl overflow-hidden bg-[#07090E] border relative"
                             :class="game.already_imported ? 'border-emerald-500/30' : 'border-[#1E2536]'">
                            <img :src="game.cover_thumb" 
                                 :alt="game.title" 
                                 loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">

                            <!-- Status Badge Overlay: Anti-Duplicates -->
                            <div class="absolute top-1.5 right-1.5">
                                <template x-if="game.already_imported">
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-950/90 border border-emerald-500/50 text-emerald-300 text-[9px] font-mono font-bold flex items-center gap-1 shadow-md backdrop-blur-md">
                                        <i data-lucide="check-circle-2" class="w-3 h-3 text-emerald-400"></i>
                                        <span>En Catálogo</span>
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Game Info -->
                        <div>
                            <h3 class="text-xs font-bold font-sans line-clamp-2 leading-snug transition-colors"
                                :class="game.already_imported ? 'text-emerald-100 group-hover:text-emerald-300' : (provider === 'romsemu' ? 'text-white group-hover:text-rose-300' : (provider === 'cdromance' ? 'text-white group-hover:text-blue-300' : 'text-white group-hover:text-purple-300'))"
                                :title="game.title"
                                x-text="game.title"></h3>
                            
                            <div class="flex items-center gap-1.5 mt-1.5">
                                <span class="text-[9px] font-mono uppercase px-2 py-0.5 rounded-md font-bold"
                                      :class="provider === 'romsemu' 
                                        ? 'bg-rose-950/60 text-rose-300 border border-rose-500/25' 
                                        : (provider === 'cdromance' 
                                            ? 'bg-blue-950/60 text-blue-300 border border-blue-500/25' 
                                            : 'bg-purple-950/60 text-purple-300 border border-purple-500/25')"
                                      x-text="game.console_badge || game.console_slug">
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Action Buttons -->
                    <div class="pt-3 mt-2 border-t"
                         :class="game.already_imported ? 'border-emerald-500/20' : 'border-[#1E2536]'">
                        
                        <!-- If already in DB: Edit + Public View links -->
                        <template x-if="game.already_imported">
                            <div class="flex items-center gap-1.5 w-full">
                                <a :href="game.edit_url" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="flex-1 py-1.5 px-2 rounded-xl bg-emerald-950/60 hover:bg-emerald-900/80 border border-emerald-500/40 text-emerald-300 hover:text-white text-[11px] font-mono font-bold transition-all flex items-center justify-center gap-1 shadow-sm"
                                   title="Editar ficha en administración">
                                    <i data-lucide="edit-3" class="w-3 h-3"></i>
                                    <span>Editar Ficha</span>
                                </a>
                                <template x-if="game.view_url">
                                    <a :href="game.view_url" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="p-1.5 rounded-xl bg-[#090C12] hover:bg-[#141824] border border-[#1E2536] text-gray-300 hover:text-white text-xs font-mono transition-all flex items-center justify-center shadow-sm"
                                       title="Ver ficha pública del juego">
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                    </a>
                                </template>
                            </div>
                        </template>

                        <!-- If not in DB: 1-Click Import Button (with Anti-Saturation Safe Queue) -->
                        <template x-if="!game.already_imported">
                            <button type="button" 
                                    @click="enqueueImport(game)"
                                    :disabled="isGameQueuedOrImporting(game.url)"
                                    class="w-full py-2 rounded-xl text-white text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-md cursor-pointer disabled:opacity-50 disabled:cursor-wait"
                                    :class="provider === 'romsemu' 
                                        ? 'bg-rose-600 hover:bg-rose-500 shadow-rose-600/30' 
                                        : (provider === 'cdromance' 
                                            ? 'bg-blue-600 hover:bg-blue-500 shadow-blue-600/30' 
                                            : 'bg-purple-600 hover:bg-purple-500 shadow-purple-600/30')">
                                <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin" x-show="activeImportUrl === game.url"></i>
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-300" x-show="importQueue.includes(game.url)"></i>
                                <i data-lucide="zap" class="w-3.5 h-3.5" x-show="!isGameQueuedOrImporting(game.url)"></i>
                                <span x-text="getImportButtonText(game.url)"></span>
                            </button>
                        </template>

                    </div>

                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && games.length === 0" class="p-16 text-center bg-[#0D111A] border border-[#1E2536] rounded-3xl space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-[#141824] border border-[#1E2536] flex items-center justify-center mx-auto text-gray-500">
                <i data-lucide="inbox" class="w-6 h-6"></i>
            </div>
            <h3 class="text-sm font-bold text-white">No se encontraron títulos en esta página</h3>
            <p class="text-xs text-gray-400 font-mono max-w-sm mx-auto">Prueba cambiando de consola o verificando la disponibilidad del proveedor.</p>
        </div>

        <!-- Pagination Controls -->
        <div class="flex items-center justify-between border-t border-[#1E2536] pt-5">
            <button type="button" 
                    @click="prevPage()" 
                    :disabled="page <= 1 || loading"
                    class="px-4 py-2.5 rounded-2xl bg-[#0D111A] hover:bg-[#141824] border border-[#1E2536] text-xs font-mono text-gray-300 hover:text-white transition-all flex items-center gap-2 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                <span>Página Anterior</span>
            </button>

            <div class="text-xs font-mono text-gray-400 flex items-center gap-2">
                <span>Página</span>
                <span class="px-3 py-1 rounded-xl bg-[#07090F] border border-[#1E2536] text-white font-bold font-mono shadow-inner" x-text="page"></span>
            </div>

            <button type="button" 
                    @click="nextPage()" 
                    :disabled="!hasNext || loading"
                    class="px-4 py-2.5 rounded-2xl bg-[#0D111A] hover:bg-[#141824] border border-[#1E2536] text-xs font-mono text-gray-300 hover:text-white transition-all flex items-center gap-2 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                <span>Página Siguiente</span>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>

    </div>

    <!-- ========================================== -->
    <!-- MODAL 1: Cargar a Cola DRAFT (Auto-Harvest) -->
    <!-- ========================================== -->
    <div x-show="openHarvestModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div @click.outside="if (!harvestLoading) openHarvestModal = false"
             class="w-full max-w-lg bg-[#0F131C] border border-[#232B3E] rounded-3xl p-6 shadow-2xl space-y-5 text-xs font-mono relative overflow-hidden"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="absolute -top-12 -right-12 w-40 h-40 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex items-center justify-between border-b border-[#1E2536] pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center">
                        <i data-lucide="download-cloud" class="w-4.5 h-4.5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white font-sans">Auto-Cargar Juegos a Cola DRAFT</h3>
                        <p class="text-[11px] text-gray-400 font-sans">Rastrea e importa títulos sin duplicar para goteo progresivo</p>
                    </div>
                </div>
                <button type="button" @click="if (!harvestLoading) openHarvestModal = false" class="text-gray-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="space-y-4">
                <!-- Fuente / Proveedor Selector -->
                <div>
                    <label class="block text-gray-400 mb-1.5 font-bold">Fuente Proveedora:</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" 
                                @click="harvestProvider = 'romsemu'"
                                class="p-2.5 rounded-xl border text-center font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
                                :class="harvestProvider === 'romsemu' ? 'bg-rose-950/40 border-rose-500 text-white shadow-sm shadow-rose-600/30' : 'bg-[#07090F] border-[#1E2536] text-gray-400 hover:text-white'">
                            <i data-lucide="cpu" class="w-4 h-4 text-rose-400"></i>
                            <span>Romsemu (PS4, Switch...)</span>
                        </button>
                        <button type="button" 
                                @click="harvestProvider = 'cdromance'"
                                class="p-2.5 rounded-xl border text-center font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
                                :class="harvestProvider === 'cdromance' ? 'bg-blue-950/40 border-blue-500 text-white shadow-sm shadow-blue-600/30' : 'bg-[#07090F] border-[#1E2536] text-gray-400 hover:text-white'">
                            <i data-lucide="disc-3" class="w-4 h-4 text-blue-400"></i>
                            <span>CDRomance (PSP, PS2...)</span>
                        </button>
                    </div>
                </div>

                <!-- Consola Target -->
                <div>
                    <label class="block text-gray-400 mb-1.5 font-bold">Ecosistema / Consola:</label>
                    <select x-model="harvestConsole" class="w-full bg-[#07090F] border border-[#1E2536] rounded-xl p-2.5 text-white focus:outline-none focus:border-amber-500">
                        <option value="all">⭐ Todos los Sistemas (Catálogo General Variado)</option>
                        <template x-for="c in (consolesByProvider[harvestProvider] || [])" :key="c.slug">
                            <option :value="c.slug" x-text="c.name" x-show="c.slug !== 'all'"></option>
                        </template>
                    </select>
                </div>

                <!-- Cantidad a Cosechar -->
                <div>
                    <label class="block text-gray-400 mb-1.5 font-bold">Cantidad de títulos a extraer:</label>
                    <div class="grid grid-cols-4 gap-2">
                        <button type="button" @click="harvestLimit = 5" :class="harvestLimit === 5 ? 'bg-amber-600 text-white font-bold' : 'bg-[#07090F] border border-[#1E2536] text-gray-400 hover:text-white'" class="py-2 rounded-xl transition-all cursor-pointer">5 ROMs</button>
                        <button type="button" @click="harvestLimit = 10" :class="harvestLimit === 10 ? 'bg-amber-600 text-white font-bold' : 'bg-[#07090F] border border-[#1E2536] text-gray-400 hover:text-white'" class="py-2 rounded-xl transition-all cursor-pointer">10 ROMs</button>
                        <button type="button" @click="harvestLimit = 15" :class="harvestLimit === 15 ? 'bg-amber-600 text-white font-bold' : 'bg-[#07090F] border border-[#1E2536] text-gray-400 hover:text-white'" class="py-2 rounded-xl transition-all cursor-pointer">15 ROMs</button>
                        <button type="button" @click="harvestLimit = 20" :class="harvestLimit === 20 ? 'bg-amber-600 text-white font-bold' : 'bg-[#07090F] border border-[#1E2536] text-gray-400 hover:text-white'" class="py-2 rounded-xl transition-all cursor-pointer">20 ROMs</button>
                    </div>
                </div>

                <div class="p-3 bg-[#07090F] border border-[#1E2536] rounded-xl text-[11px] text-gray-400 space-y-1">
                    <p class="flex items-center gap-1.5 text-emerald-400 font-bold">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        <span>Filtro Anti-Duplicados Activo</span>
                    </p>
                    <p>Los juegos existentes se ignoran automáticamente. Las carátulas se procesan a WebP local para máxima velocidad.</p>
                </div>

                <!-- Feedback en el modal -->
                <div x-show="harvestNotice" class="p-3 rounded-xl border text-xs"
                     :class="harvestNoticeSuccess ? 'bg-emerald-950/70 border-emerald-500/40 text-emerald-300' : 'bg-rose-950/70 border-rose-500/40 text-rose-300'">
                    <span x-text="harvestNotice"></span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#1E2536]">
                <button type="button" 
                        @click="openHarvestModal = false" 
                        :disabled="harvestLoading"
                        class="px-4 py-2.5 rounded-xl bg-[#07090F] hover:bg-[#141824] border border-[#1E2536] text-gray-400 hover:text-white cursor-pointer disabled:opacity-50">
                    Cancelar
                </button>
                <button type="button" 
                        @click="triggerAutoHarvest()" 
                        :disabled="harvestLoading"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold shadow-lg shadow-amber-600/30 flex items-center gap-2 cursor-pointer disabled:opacity-50">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="harvestLoading"></i>
                    <i data-lucide="sparkles" class="w-4 h-4" x-show="!harvestLoading"></i>
                    <span x-text="harvestLoading ? 'Cosechando Bóveda...' : 'Iniciar Carga a Cola'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- MODAL 2: Ajustes Piloto Automático          -->
    <!-- ========================================== -->
    <div x-show="openSettingsModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div @click.outside="if (!settingsLoading) openSettingsModal = false"
             class="w-full max-w-lg bg-[#0F131C] border border-[#232B3E] rounded-3xl p-6 shadow-2xl space-y-5 text-xs font-mono relative overflow-hidden"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="absolute -top-12 -right-12 w-40 h-40 bg-purple-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex items-center justify-between border-b border-[#1E2536] pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-purple-500/20 text-purple-400 border border-purple-500/30 flex items-center justify-center">
                        <i data-lucide="sliders" class="w-4.5 h-4.5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white font-sans">Ajustes del Piloto Automático</h3>
                        <p class="text-[11px] text-gray-400 font-sans">Controla la cadencia, volumen y automatización con IA</p>
                    </div>
                </div>
                <button type="button" @click="if (!settingsLoading) openSettingsModal = false" class="text-gray-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="space-y-4">
                <!-- Toggle Estado Maestro -->
                <div class="flex items-center justify-between p-3.5 rounded-2xl bg-[#07090F] border border-[#1E2536]">
                    <div>
                        <span class="font-bold text-white block">Motor de Publicación Automática</span>
                        <span class="text-[11px] text-gray-400">Activa o pausa el cron en segundo plano</span>
                    </div>
                    <button type="button" 
                            @click="autopilotSettings.autopilot_enabled = !autopilotSettings.autopilot_enabled"
                            class="relative shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"
                            style="width: 48px; height: 26px; min-width: 48px; min-height: 26px;"
                            :class="autopilotSettings.autopilot_enabled ? 'bg-emerald-500' : 'bg-gray-800'">
                        <span class="pointer-events-none absolute top-[3px] rounded-full bg-white shadow-md transition-all duration-200 ease-in-out"
                              style="width: 20px; height: 20px;"
                              :style="autopilotSettings.autopilot_enabled ? 'left: 25px;' : 'left: 3px;'"></span>
                    </button>
                </div>

                <!-- Cantidad por Tanda -->
                <div>
                    <label class="block text-gray-400 mb-1.5 font-bold">¿Cuántos juegos publicar por tanda?:</label>
                    <select x-model.number="autopilotSettings.posts_per_batch" class="w-full bg-[#07090F] border border-[#1E2536] rounded-xl p-2.5 text-white focus:outline-none focus:border-purple-500 font-mono">
                        <option value="2">2 juegos por lote</option>
                        <option value="4">4 juegos por lote (Recomendado)</option>
                        <option value="6">6 juegos por lote</option>
                        <option value="8">8 juegos por lote</option>
                        <option value="12">12 juegos por lote</option>
                        <option value="16">16 juegos por lote</option>
                        <option value="20">20 juegos por lote</option>
                    </select>
                </div>

                <!-- Frecuencia de publicación -->
                <div>
                    <label class="block text-gray-400 mb-1.5 font-bold">Intervalo de tiempo (Frecuencia):</label>
                    <select x-model.number="autopilotSettings.batch_interval_hours" class="w-full bg-[#07090F] border border-[#1E2536] rounded-xl p-2.5 text-white focus:outline-none focus:border-purple-500 font-mono">
                        <option value="1">Cada 1 hora (Alta frecuencia)</option>
                        <option value="2">Cada 2 horas (Recomendado SEO)</option>
                        <option value="4">Cada 4 horas</option>
                        <option value="6">Cada 6 horas</option>
                        <option value="12">Cada 12 horas (2 veces al día)</option>
                        <option value="24">Cada 24 horas (1 vez al día)</option>
                    </select>
                </div>

                <!-- Enriquecimiento con IA -->
                <div class="flex items-center justify-between p-3.5 rounded-2xl bg-[#07090F] border border-[#1E2536]">
                    <div>
                        <span class="font-bold text-white block">Redacción Enriquecida con Gemini IA</span>
                        <span class="text-[11px] text-gray-400">Genera sinopsis de 200+ palabras y tags SEO al publicar</span>
                    </div>
                    <button type="button" 
                            @click="autopilotSettings.auto_ai_enrich = !autopilotSettings.auto_ai_enrich"
                            class="relative shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"
                            style="width: 48px; height: 26px; min-width: 48px; min-height: 26px;"
                            :class="autopilotSettings.auto_ai_enrich ? 'bg-purple-600' : 'bg-gray-800'">
                        <span class="pointer-events-none absolute top-[3px] rounded-full bg-white shadow-md transition-all duration-200 ease-in-out"
                              style="width: 20px; height: 20px;"
                              :style="autopilotSettings.auto_ai_enrich ? 'left: 25px;' : 'left: 3px;'"></span>
                    </button>
                </div>

                <!-- Feedback en el modal -->
                <div x-show="settingsNotice" class="p-3 rounded-xl border text-xs"
                     :class="settingsNoticeSuccess ? 'bg-emerald-950/70 border-emerald-500/40 text-emerald-300' : 'bg-rose-950/70 border-rose-500/40 text-rose-300'">
                    <span x-text="settingsNotice"></span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#1E2536]">
                <button type="button" 
                        @click="openSettingsModal = false" 
                        :disabled="settingsLoading"
                        class="px-4 py-2.5 rounded-xl bg-[#07090F] hover:bg-[#141824] border border-[#1E2536] text-gray-400 hover:text-white cursor-pointer disabled:opacity-50">
                    Cancelar
                </button>
                <button type="button" 
                        @click="saveAutopilotSettings()" 
                        :disabled="settingsLoading"
                        class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold shadow-lg shadow-purple-600/30 flex items-center gap-2 cursor-pointer disabled:opacity-50">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="settingsLoading"></i>
                    <i data-lucide="check" class="w-4 h-4" x-show="!settingsLoading"></i>
                    <span x-text="settingsLoading ? 'Guardando...' : 'Guardar Ajustes'"></span>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function scraperCatalogApp() {
    return {
        provider: 'romsemu',
        consoleSlug: 'all',
        page: 1,
        consolesByProvider: {
            romsemu: [
                { slug: 'all', name: '⭐ Todos los Sistemas (Últimos Agregados)' },
                { slug: 'nintendo-3ds', name: 'Nintendo 3DS' },
                { slug: 'nintendo-switch', name: 'Nintendo Switch' },
                { slug: 'sega-sg-1000', name: 'Sega SG-1000' },
                { slug: 'sega-32x', name: 'Sega 32X' },
                { slug: 'playstation-4', name: 'PlayStation 4' },
                { slug: 'playstation-vita', name: 'PlayStation Vita' },
            ],
            cdromance: [
                { slug: 'all', name: '⭐ Todos los Sistemas (Últimos Agregados)' },
                { slug: 'psp', name: 'PlayStation Portable (PSP)' },
                { slug: 'playstation-2', name: 'PlayStation 2 (PS2)' },
                { slug: 'playstation', name: 'PlayStation 1 (PSX)' },
                { slug: 'gamecube', name: 'Nintendo GameCube' },
                { slug: 'game-boy-advance', name: 'Game Boy Advance (GBA)' },
                { slug: 'nintendo-ds', name: 'Nintendo DS (NDS)' },
                { slug: 'super-nintendo', name: 'Super Nintendo (SNES)' },
                { slug: 'nintendo-64', name: 'Nintendo 64 (N64)' },
            ],
            romspedia: [
                { slug: 'psp', name: 'PlayStation Portable (PSP)' },
                { slug: 'playstation-2', name: 'PlayStation 2 (PS2)' },
                { slug: 'playstation', name: 'PlayStation 1 (PSX)' },
                { slug: 'gamecube', name: 'Nintendo GameCube' },
                { slug: 'game-boy-advance', name: 'Game Boy Advance (GBA)' },
                { slug: 'nintendo-ds', name: 'Nintendo DS (NDS)' },
                { slug: 'super-nintendo', name: 'Super Nintendo (SNES)' },
                { slug: 'nintendo-64', name: 'Nintendo 64 (N64)' },
            ],
        },

        get currentConsoles() {
            return this.consolesByProvider[this.provider] || [];
        },

        getConsoleName(slug) {
            if (slug === 'all') return 'Todos los Sistemas';
            const found = this.currentConsoles.find(c => c.slug === slug);
            return found ? found.name : slug.replace(/-/g, ' ').toUpperCase();
        },
        games: [],
        loading: false,
        hasNext: true,
        importStatus: 'DRAFT',
        optimizeCover: true,
        generateAi: true,
        feedbackMessage: '',
        feedbackSuccess: true,
        lastImportedEditUrl: null,
        autopilotQueueCount: {{ \App\Models\Game::where('status', 'DRAFT')->count() }},
        dripLoading: false,
        dripNotice: '',
        dripNoticeSuccess: true,

        // Modales de Control & Ajustes
        openHarvestModal: false,
        harvestProvider: 'romsemu',
        harvestConsole: 'all',
        harvestLimit: {{ (int) \App\Models\Setting::get('roms_auto_harvest_limit', 10) }},
        harvestLoading: false,
        harvestNotice: '',
        harvestNoticeSuccess: true,

        openSettingsModal: false,
        settingsLoading: false,
        settingsNotice: '',
        settingsNoticeSuccess: true,
        autopilotSettings: {
            autopilot_enabled: {{ \App\Models\Setting::get('roms_autopilot_enabled', config('roms.autopilot_enabled', true)) ? 'true' : 'false' }},
            posts_per_batch: {{ (int) \App\Models\Setting::get('roms_posts_per_batch', config('roms.posts_per_batch', 4)) }},
            batch_interval_hours: {{ (int) \App\Models\Setting::get('roms_batch_interval_hours', config('roms.batch_interval_hours', 2)) }},
            auto_ai_enrich: {{ \App\Models\Setting::get('roms_auto_ai_enrich', config('roms.auto_ai_enrich', true)) ? 'true' : 'false' }},
            auto_harvest_limit: {{ (int) \App\Models\Setting::get('roms_auto_harvest_limit', 10) }}
        },

        async triggerDripNow() {
            if (this.dripLoading) return;
            if (this.autopilotQueueCount === 0) {
                this.dripNotice = '⚠️ No hay juegos en estado DRAFT en la cola. Carga juegos con "Cargar a Cola DRAFT" o usa "1-Clic Importar".';
                this.dripNoticeSuccess = false;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                return;
            }

            const batchCount = this.autopilotSettings.posts_per_batch || 4;
            this.dripLoading = true;
            this.dripNotice = `⏳ Procesando y publicando lote de ${batchCount} juegos en vivo...`;
            this.dripNoticeSuccess = true;
            this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });

            try {
                const res = await fetch('{{ route("admin.scraper.drip_now") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ count: batchCount })
                });
                const data = await res.json();
                if (data.success) {
                    this.dripNotice = data.message || `¡Tanda de ${batchCount} juegos publicada con éxito!`;
                    this.dripNoticeSuccess = true;
                    if (data.remaining_drafts !== undefined) {
                        this.autopilotQueueCount = data.remaining_drafts;
                    }
                    this.loadCatalog();
                } else {
                    this.dripNotice = data.message || 'No se pudo publicar la tanda.';
                    this.dripNoticeSuccess = false;
                }
            } catch (e) {
                this.dripNotice = 'Error al conectar con el servidor: ' + e.message;
                this.dripNoticeSuccess = false;
            } finally {
                this.dripLoading = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        async triggerAutoHarvest() {
            if (this.harvestLoading) return;
            this.harvestLoading = true;
            this.harvestNotice = '⏳ Explorando catálogo y cosechando títulos a la cola DRAFT (Anti-Duplicados)...';
            this.harvestNoticeSuccess = true;

            try {
                const res = await fetch('{{ route("admin.scraper.auto_harvest_draft") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        provider: this.harvestProvider,
                        console_slug: this.harvestConsole,
                        limit: this.harvestLimit
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.harvestNotice = data.message;
                    this.harvestNoticeSuccess = true;
                    if (data.total_drafts !== undefined) {
                        this.autopilotQueueCount = data.total_drafts;
                    }
                    this.loadCatalog();
                    setTimeout(() => {
                        this.openHarvestModal = false;
                        this.harvestNotice = '';
                    }, 2000);
                } else {
                    this.harvestNotice = data.message || 'No se pudo completar la carga a cola.';
                    this.harvestNoticeSuccess = false;
                }
            } catch (e) {
                this.harvestNotice = 'Error al conectar con el servidor: ' + e.message;
                this.harvestNoticeSuccess = false;
            } finally {
                this.harvestLoading = false;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            }
        },

        async saveAutopilotSettings() {
            if (this.settingsLoading) return;
            this.settingsLoading = true;
            this.settingsNotice = '⏳ Guardando nueva configuración en vivo...';
            this.settingsNoticeSuccess = true;

            try {
                const res = await fetch('{{ route("admin.scraper.autopilot_settings.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.autopilotSettings)
                });
                const data = await res.json();
                if (data.success) {
                    this.settingsNotice = data.message;
                    this.settingsNoticeSuccess = true;
                    if (data.settings) {
                        this.autopilotSettings = data.settings;
                    }
                    setTimeout(() => {
                        this.openSettingsModal = false;
                        this.settingsNotice = '';
                    }, 1200);
                } else {
                    this.settingsNotice = data.message || 'Error al guardar los ajustes.';
                    this.settingsNoticeSuccess = false;
                }
            } catch (e) {
                this.settingsNotice = 'Error al conectar: ' + e.message;
                this.settingsNoticeSuccess = false;
            } finally {
                this.settingsLoading = false;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            }
        },

        // Anti-Bloqueo & Anti-Saturación Safe State
        cooldownActive: false,
        cooldownSeconds: 0,
        cooldownMessage: '',
        cooldownTimer: null,
        activeImportUrl: null,
        importQueue: [],
        isProcessingQueue: false,

        isGameQueuedOrImporting(url) {
            return this.activeImportUrl === url || this.importQueue.includes(url);
        },

        getImportButtonText(url) {
            if (this.activeImportUrl === url) return this.generateAi ? 'Importando & Redactando IA...' : 'Importando...';
            if (this.importQueue.includes(url)) return 'En cola segura...';
            return '1-Clic Importar';
        },

        setProvider(newProvider) {
            if (this.provider === newProvider) return;
            this.provider = newProvider;
            this.page = 1;

            const available = this.consolesByProvider[newProvider] || [];
            const exists = available.some(c => c.slug === this.consoleSlug);
            if (!exists) {
                if (newProvider === 'romspedia') {
                    this.consoleSlug = 'psp';
                } else {
                    this.consoleSlug = 'all';
                }
            }
            this.loadCatalog();
        },

        async loadCatalog() {
            this.loading = true;
            this.feedbackMessage = '';

            try {
                const url = `{{ route('admin.scraper.catalog.fetch') }}?provider=${this.provider}&console=${this.consoleSlug}&page=${this.page}`;
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                if (res.status === 429 || data.cooldown) {
                    this.startCooldownTimer(data.remaining_seconds || 60, data.message);
                    this.games = [];
                    this.feedbackMessage = data.message || 'Pausa de seguridad activada para proteger tu IP.';
                    this.feedbackSuccess = false;
                    return;
                }

                if (data.success) {
                    this.games = data.games || [];
                    this.hasNext = Boolean(data.has_next);
                } else {
                    this.games = [];
                    this.feedbackMessage = data.message || 'Error al cargar el catálogo.';
                    this.feedbackSuccess = false;
                }
            } catch (e) {
                this.feedbackMessage = 'Error en la conexión con el servidor.';
                this.feedbackSuccess = false;
            } finally {
                this.loading = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        enqueueImport(game) {
            if (this.isGameQueuedOrImporting(game.url)) return;
            this.importQueue.push(game.url);
            this.processQueue();
        },

        async processQueue() {
            if (this.isProcessingQueue) return;
            this.isProcessingQueue = true;

            while (this.importQueue.length > 0) {
                const nextUrl = this.importQueue.shift();
                this.activeImportUrl = nextUrl;
                
                const gameObj = this.games.find(g => g.url === nextUrl);
                await this.executeImport(nextUrl, gameObj);

                this.activeImportUrl = null;

                // Pausa cortés de seguridad (1.2s) entre importaciones consecutivas para no saturar al proveedor
                if (this.importQueue.length > 0) {
                    await new Promise(resolve => setTimeout(resolve, 1200));
                }
            }

            this.isProcessingQueue = false;
        },

        async executeImport(url, gameObj) {
            this.feedbackMessage = '';
            this.lastImportedEditUrl = null;

            try {
                const res = await fetch('{{ route("admin.scraper.quick_import") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        url: url,
                        console_slug: (gameObj && gameObj.console_slug ? gameObj.console_slug : this.consoleSlug),
                        status: this.importStatus,
                        optimize_cover: this.optimizeCover,
                        generate_ai: this.generateAi,
                    })
                });

                const data = await res.json();

                if (res.status === 429 || data.cooldown) {
                    this.startCooldownTimer(data.remaining_seconds || 60, data.message);
                    this.feedbackSuccess = false;
                    this.feedbackMessage = data.message || 'Pausa de seguridad activada.';
                    // Vaciar cola para no seguir saturando
                    this.importQueue = [];
                    return;
                }

                if (data.success) {
                    if (gameObj) {
                        gameObj.already_imported = true;
                        gameObj.edit_url = data.edit_url;
                        gameObj.view_url = data.view_url || null;
                    }
                    this.feedbackSuccess = true;
                    this.feedbackMessage = data.message;
                    this.lastImportedEditUrl = data.edit_url;
                } else {
                    this.feedbackSuccess = false;
                    this.feedbackMessage = data.message || 'No se pudo importar el juego.';
                }
            } catch (e) {
                this.feedbackSuccess = false;
                this.feedbackMessage = 'Error al importar el juego.';
            } finally {
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        startCooldownTimer(seconds, message) {
            this.cooldownActive = true;
            this.cooldownSeconds = Math.max(1, seconds || 60);
            this.cooldownMessage = message || 'Protegiendo tu IP de posibles bloqueos.';

            if (this.cooldownTimer) clearInterval(this.cooldownTimer);
            this.cooldownTimer = setInterval(() => {
                if (this.cooldownSeconds > 1) {
                    this.cooldownSeconds--;
                } else {
                    this.cooldownActive = false;
                    this.cooldownSeconds = 0;
                    clearInterval(this.cooldownTimer);
                }
            }, 1000);
        },

        async resetCooldown() {
            try {
                const res = await fetch('{{ route("admin.scraper.reset_cooldown") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ provider: this.provider })
                });
                const data = await res.json();
                if (data.success) {
                    this.cooldownActive = false;
                    this.cooldownSeconds = 0;
                    if (this.cooldownTimer) clearInterval(this.cooldownTimer);
                    this.feedbackSuccess = true;
                    this.feedbackMessage = 'Pausa de seguridad restablecida. Puedes continuar.';
                    this.loadCatalog();
                }
            } catch (e) {
                // error
            }
        },

        prevPage() {
            if (this.page > 1) {
                this.page--;
                this.loadCatalog();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        nextPage() {
            if (this.hasNext) {
                this.page++;
                this.loadCatalog();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
    };
}
</script>
@endsection
