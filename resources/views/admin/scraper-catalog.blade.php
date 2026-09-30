@extends('layouts.admin')

@section('title', 'Explorador de Catálogo & Importador 1-Clic — ROMHUB')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="scraperCatalogApp()" x-init="loadCatalog()">

    <!-- Top Breadcrumb & Mode Switcher -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#232936] pb-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-gray-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-300">Admin</a>
                <span>/</span>
                <span class="text-purple-400 font-semibold">Laboratorio</span>
                <span>/</span>
                <span class="text-gray-300">Catálogo 1-Clic</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight font-heading flex flex-wrap items-center gap-2.5">
                <span class="p-2 rounded-xl bg-purple-500/20 text-purple-400 border border-purple-500/30">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </span>
                <span>Catálogo en 1 Clic</span>
                <span class="text-xs px-2.5 py-1 rounded-full font-mono uppercase tracking-wider font-bold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                    Importación Ultrarrápida
                </span>
            </h1>
        </div>

        <!-- Mode Navigation Tabs & Safety Badge -->
        <div class="flex flex-wrap items-center gap-2">
            <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-400 text-xs font-mono shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Anti-Bloqueo:</span>
                <span class="font-bold text-white">Activo (Cola Segura & Rate Limiting)</span>
            </div>

            <div class="flex items-center gap-1.5 p-1 bg-[#11141A] border border-[#232936] rounded-xl shadow-md">
                <a href="{{ route('admin.scraper.catalog') }}" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-mono font-bold bg-purple-600 text-white shadow-md shadow-purple-600/30 flex items-center gap-1.5">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    <span>Catálogo 1-Clic</span>
                </a>
                <a href="{{ route('admin.scraper.demo') }}" 
                   class="px-3.5 py-1.5 rounded-lg text-xs font-mono font-bold text-gray-400 hover:text-white hover:bg-[#171B22] flex items-center gap-1.5 transition-colors">
                    <i data-lucide="zap" class="w-3.5 h-3.5 text-purple-400"></i>
                    <span>Extractor por URL</span>
                </a>
            </div>
        </div>
    </div>
 
    <!-- Autopilot & Drip-Feed SEO Control Banner -->
    <div class="bg-gradient-to-r from-[#171B26] via-[#121620] to-[#171B26] border border-purple-500/25 rounded-2xl p-5 shadow-2xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-purple-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        Piloto Automático Activo
                    </span>
                    <span class="text-xs font-mono text-gray-300">
                        Publicando <strong class="text-white font-bold">{{ config('roms.posts_per_batch', 4) }} juegos</strong> cada <strong class="text-purple-400">{{ config('roms.batch_interval_hours', 2) }} horas</strong>
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-mono bg-blue-500/15 text-blue-300 border border-blue-500/30">
                        <i data-lucide="shield-check" class="w-3 h-3"></i>
                        Anti-Duplicados Estricto (Google Safe)
                    </span>
                </div>
                <p class="text-xs text-gray-400 leading-relaxed max-w-3xl">
                    Los juegos extraídos desde <strong>CDRomance</strong> y <strong>Romspedia</strong> se filtran para descartar duplicados y se encolan en <code>DRAFT</code>. El cron automático los publica gradualmente para simular crecimiento orgánico y evitar penalizaciones de Google.
                </p>
            </div>

            <!-- Quick Action & Counter -->
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <div class="px-4 py-2 bg-[#0A0C0F] border border-[#232936] rounded-xl text-center">
                    <div class="text-[10px] uppercase font-mono text-gray-500">En Cola DRAFT</div>
                    <div class="text-lg font-black text-amber-400 font-mono" x-text="autopilotQueueCount">
                        {{ \App\Models\Game::where('status', 'DRAFT')->count() }}
                    </div>
                </div>

                <button type="button"
                        @click="triggerDripNow()"
                        :disabled="dripLoading"
                        class="px-4 py-2.5 rounded-xl text-xs font-mono font-bold bg-purple-600 hover:bg-purple-500 active:scale-95 text-white shadow-lg shadow-purple-600/30 flex items-center gap-2 transition-all cursor-pointer disabled:opacity-50">
                    <i data-lucide="send" class="w-3.5 h-3.5" :class="{'animate-spin': dripLoading}"></i>
                    <span x-text="dripLoading ? 'Publicando...' : 'Publicar 4 Ahora'"></span>
                </button>
            </div>
        </div>

        <!-- Alert Notification for Drip -->
        <div x-show="dripNotice" x-transition class="mt-4 p-3.5 rounded-xl text-xs font-mono flex items-center justify-between gap-3 border shadow-lg"
             :class="dripNoticeSuccess ? 'bg-emerald-950/80 border-emerald-500/40 text-emerald-300' : 'bg-amber-950/80 border-amber-500/40 text-amber-300'">
            <div class="flex items-center gap-2.5">
                <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                <span x-text="dripNotice" class="font-bold"></span>
            </div>
            <button type="button" @click="dripNotice = ''" class="text-gray-400 hover:text-white px-2 py-0.5 rounded cursor-pointer font-bold">&times;</button>
        </div>
    </div>

    <!-- Filters & Settings Bar -->
    <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-5 shadow-xl space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            <!-- Left: Provider Tabs & Console Select -->
            <div class="flex flex-wrap items-center gap-3">
                
                <!-- Provider Selector -->
                <div class="flex items-center gap-1 bg-[#0A0C0F] p-1 rounded-xl border border-[#232936]">
                    <button type="button" 
                            @click="setProvider('romspedia')" 
                            class="px-3.5 py-1.5 rounded-lg text-xs font-mono font-bold transition-all flex items-center gap-1.5 cursor-pointer"
                            :class="provider === 'romspedia' ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30' : 'text-gray-400 hover:text-white'">
                        <i data-lucide="gamepad" class="w-3.5 h-3.5"></i>
                        <span>Romspedia</span>
                    </button>
                    <button type="button" 
                            @click="setProvider('cdromance')" 
                            class="px-3.5 py-1.5 rounded-lg text-xs font-mono font-bold transition-all flex items-center gap-1.5 cursor-pointer"
                            :class="provider === 'cdromance' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-gray-400 hover:text-white'">
                        <i data-lucide="disc" class="w-3.5 h-3.5"></i>
                        <span>CDRomance</span>
                    </button>
                    <button type="button" 
                            @click="setProvider('romsemu')" 
                            class="px-3.5 py-1.5 rounded-lg text-xs font-mono font-bold transition-all flex items-center gap-1.5 cursor-pointer"
                            :class="provider === 'romsemu' ? 'bg-red-600 text-white shadow-md shadow-red-600/30' : 'text-gray-400 hover:text-white'">
                        <i data-lucide="cpu" class="w-3.5 h-3.5"></i>
                        <span>Romsemu</span>
                    </button>
                </div>

                <!-- Console Selector Dropdown -->
                <div class="relative min-w-[240px]">
                    <select x-model="consoleSlug" 
                            @change="page = 1; loadCatalog()"
                            class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-purple-500 font-mono transition-colors">
                        <option value="all" style="background-color: #11141A;" :selected="provider === 'cdromance'">⭐ Todos los Sistemas (Últimos Agregados)</option>
                        <option value="nintendo-switch" style="background-color: #11141A;">Nintendo Switch</option>
                        <option value="nintendo-3ds" style="background-color: #11141A;">Nintendo 3DS</option>
                        <option value="psp" style="background-color: #11141A;">PlayStation Portable (PSP)</option>
                        <option value="playstation-2" style="background-color: #11141A;">PlayStation 2 (PS2)</option>
                        <option value="playstation" style="background-color: #11141A;">PlayStation 1 (PSX)</option>
                        <option value="gamecube" style="background-color: #11141A;">Nintendo GameCube</option>
                        <option value="game-boy-advance" style="background-color: #11141A;">Game Boy Advance (GBA)</option>
                        <option value="nintendo-ds" style="background-color: #11141A;">Nintendo DS (NDS)</option>
                        <option value="super-nintendo" style="background-color: #11141A;">Super Nintendo (SNES)</option>
                        <option value="nintendo-64" style="background-color: #11141A;">Nintendo 64 (N64)</option>
                    </select>
                </div>

                <!-- Refresh Button -->
                <button type="button" 
                        @click="loadCatalog()" 
                        :disabled="loading"
                        class="p-2 rounded-xl bg-[#171B22] hover:bg-[#232936] border border-[#232936] text-gray-300 hover:text-white transition-colors cursor-pointer disabled:opacity-50"
                        title="Refrescar catálogo">
                    <i data-lucide="refresh-cw" class="w-4 h-4" :class="loading ? 'animate-spin text-purple-400' : ''"></i>
                </button>
            </div>

            <!-- Right: Quick Import Options (Status, AI Auto-Pilot & Local Optimization) -->
            <div class="flex flex-wrap items-center gap-3 text-xs font-mono">
                <!-- Piloto Automático IA Toggle -->
                <label class="flex items-center gap-1.5 cursor-pointer text-purple-300 hover:text-purple-200 select-none bg-purple-950/40 border border-purple-500/30 px-2.5 py-1.5 rounded-xl transition-colors shadow-sm"
                       title="Genera automáticamente la sinopsis enriquecida (~200 palabras) y meta tags SEO con Gemini IA">
                    <input type="checkbox" x-model="generateAi" class="rounded bg-[#0A0C0F] border-purple-500 text-purple-600 focus:ring-purple-500 cursor-pointer">
                    <span class="flex items-center gap-1 font-bold">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-400"></i>
                        <span>Piloto Automático IA</span>
                    </span>
                </label>

                <!-- Estado Selector -->
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 font-bold">Estado:</span>
                    <select x-model="importStatus" class="bg-[#0A0C0F] border border-[#232936] rounded-xl px-2.5 py-1.5 text-xs text-white focus:outline-none">
                        <option value="DRAFT" style="background-color: #11141A;">Borrador (DRAFT)</option>
                        <option value="PUBLISHED" style="background-color: #11141A;">Publicado (PUBLISHED)</option>
                    </select>
                </div>

                <!-- WebP Convert Checkbox -->
                <label class="flex items-center gap-1.5 cursor-pointer text-gray-300 select-none bg-[#0A0C0F] border border-[#232936] px-2.5 py-1.5 rounded-xl">
                    <input type="checkbox" x-model="optimizeCover" class="rounded bg-[#0A0C0F] border-[#232936] text-purple-600 focus:ring-purple-500 cursor-pointer">
                    <span>WebP local</span>
                </label>
            </div>

        </div>
    </div>

    <!-- Feedback Message Banner -->
    <div x-show="feedbackMessage" x-cloak class="p-4 rounded-xl border text-xs flex items-center justify-between gap-3 shadow-lg"
         :class="feedbackSuccess ? 'bg-emerald-950/70 border-emerald-500/40 text-emerald-300' : 'bg-rose-950/70 border-rose-500/40 text-rose-300'">
        <div class="flex items-center gap-2">
            <i :data-lucide="feedbackSuccess ? 'check-circle-2' : 'alert-circle'" class="w-4 h-4 shrink-0"></i>
            <span x-text="feedbackMessage"></span>
        </div>
        <template x-if="lastImportedEditUrl">
            <a :href="lastImportedEditUrl" target="_blank" rel="noopener noreferrer" class="font-bold underline text-white hover:text-emerald-200 shrink-0 flex items-center gap-1 font-mono">
                <span>Editar Ficha</span>
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
            </a>
        </template>
    </div>

    <!-- Cooldown / Anti-Ban Safety Alert Banner -->
    <div x-show="cooldownActive" x-cloak class="p-4 rounded-xl border border-amber-500/40 bg-amber-950/70 text-amber-200 text-xs flex flex-wrap items-center justify-between gap-3 shadow-lg">
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

    <!-- Status / Order Mode Bar -->
    <div class="flex flex-wrap items-center justify-between gap-3 px-1 py-1 text-xs font-mono">
        <div class="flex items-center gap-2">
            <span class="flex h-2 w-2 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <template x-if="provider === 'cdromance'">
                <span class="text-blue-300">
                    <strong class="text-white">CDRomance:</strong> 
                    <span x-show="consoleSlug === 'all'" class="text-emerald-400 font-bold">⭐ Últimos Agregados Recientes (Todos los Sistemas)</span>
                    <span x-show="consoleSlug !== 'all'">⭐ Novedades y Últimos Agregados de <span class="text-white font-bold" x-text="consoleSlug.toUpperCase()"></span></span>
                </span>
            </template>
            <template x-if="provider === 'romspedia'">
                <span class="text-purple-300">
                    <strong class="text-white">Romspedia:</strong> 
                    <span>🏆 Top Populares & Más Descargados (<span class="text-white font-bold" x-text="consoleSlug.toUpperCase()"></span>)</span>
                </span>
            </template>
        </div>
        <div class="text-gray-400 text-[11px]" x-show="!loading && games.length > 0">
            <span class="text-white font-bold" x-text="games.length"></span> títulos en esta página
        </div>
    </div>

    <!-- Games Grid & Content -->
    <div class="space-y-6">
        
        <!-- Loading Skeletons -->
        <div x-show="loading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <template x-for="i in 12" :key="i">
                <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-2.5 space-y-2.5 animate-pulse">
                    <div class="aspect-[3/4] bg-[#171B22] rounded-xl w-full"></div>
                    <div class="h-3 bg-[#171B22] rounded w-3/4"></div>
                    <div class="h-7 bg-[#171B22] rounded-xl w-full"></div>
                </div>
            </template>
        </div>

        <!-- Games Cards List -->
        <div x-show="!loading && games.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <template x-for="(game, idx) in games" :key="game.url">
                <div class="border rounded-2xl p-2.5 flex flex-col justify-between transition-all group relative overflow-hidden shadow-lg"
                     :class="game.already_imported ? 'bg-[#0A1211] border-emerald-500/40 shadow-emerald-950/20 ring-1 ring-emerald-500/20' : 'bg-[#11141A] border-[#232936] hover:border-gray-600'">
                    
                    <!-- Top: Cover & Thumbnail -->
                    <div class="space-y-2.5">
                        <div class="aspect-[3/4] w-full rounded-xl overflow-hidden bg-[#0A0C0F] border relative"
                             :class="game.already_imported ? 'border-emerald-500/30' : 'border-[#232936]'">
                            <img :src="game.cover_thumb" 
                                 :alt="game.title" 
                                 loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">

                            <!-- Status Badge Overlay: Anti-Duplicates -->
                            <div class="absolute top-1.5 right-1.5">
                                <template x-if="game.already_imported">
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-950/90 border border-emerald-500/50 text-emerald-300 text-[9px] font-mono font-bold flex items-center gap-1 shadow-md backdrop-blur-sm">
                                        <i data-lucide="check-circle-2" class="w-3 h-3 text-emerald-400"></i>
                                        <span>En Catálogo</span>
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Game Info -->
                        <div>
                            <h3 class="text-xs font-bold font-sans line-clamp-2 leading-snug transition-colors"
                                :class="game.already_imported ? 'text-emerald-100 group-hover:text-emerald-300' : 'text-white group-hover:text-purple-300'"
                                :title="game.title"
                                x-text="game.title"></h3>
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="text-[9px] font-mono uppercase px-1.5 py-0.5 rounded font-bold"
                                      :class="provider === 'romspedia' ? 'bg-purple-950/60 text-purple-300 border border-purple-500/20' : 'bg-blue-950/60 text-blue-300 border border-blue-500/20'"
                                      x-text="game.console_badge || game.console_slug">
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Action Buttons -->
                    <div class="pt-3 mt-2 border-t"
                         :class="game.already_imported ? 'border-emerald-500/20' : 'border-[#232936]'">
                        
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
                                       class="p-1.5 rounded-xl bg-[#171B22] hover:bg-[#232936] border border-[#232936] text-gray-300 hover:text-white text-xs font-mono transition-all flex items-center justify-center shadow-sm"
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
                                    :class="provider === 'romspedia' ? 'bg-purple-600 hover:bg-purple-500 shadow-purple-600/20' : 'bg-blue-600 hover:bg-blue-500 shadow-blue-600/20'">
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
        <div x-show="!loading && games.length === 0" class="p-12 text-center bg-[#11141A] border border-[#232936] rounded-2xl space-y-3">
            <i data-lucide="inbox" class="w-10 h-10 text-gray-500 mx-auto"></i>
            <h3 class="text-sm font-bold text-white">No se encontraron títulos en esta página</h3>
            <p class="text-xs text-gray-400 font-mono">Prueba cambiando de consola o verificando la conexión.</p>
        </div>

        <!-- Pagination Controls -->
        <div class="flex items-center justify-between border-t border-[#232936] pt-4">
            <button type="button" 
                    @click="prevPage()" 
                    :disabled="page <= 1 || loading"
                    class="px-4 py-2 rounded-xl bg-[#11141A] hover:bg-[#171B22] border border-[#232936] text-xs font-mono text-gray-300 hover:text-white transition-colors flex items-center gap-2 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                <span>Página Anterior</span>
            </button>

            <div class="text-xs font-mono text-gray-400 flex items-center gap-2">
                <span>Página</span>
                <span class="px-2.5 py-1 rounded-lg bg-[#0A0C0F] border border-[#232936] text-purple-400 font-bold" x-text="page"></span>
            </div>

            <button type="button" 
                    @click="nextPage()" 
                    :disabled="!hasNext || loading"
                    class="px-4 py-2 rounded-xl bg-[#11141A] hover:bg-[#171B22] border border-[#232936] text-xs font-mono text-gray-300 hover:text-white transition-colors flex items-center gap-2 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                <span>Página Siguiente</span>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>

    </div>

</div>

<script>
function scraperCatalogApp() {
    return {
        provider: 'romspedia',
        consoleSlug: 'psp',
        page: 1,
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

        async triggerDripNow() {
            if (this.dripLoading) return;
            if (this.autopilotQueueCount === 0) {
                this.dripNotice = '⚠️ No hay juegos en estado DRAFT en la cola. Haz clic en "1-Clic Importar" en cualquier juego del catálogo inferior para ponerlo en fila.';
                this.dripNoticeSuccess = false;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
                return;
            }

            this.dripLoading = true;
            this.dripNotice = '⏳ Procesando y publicando lote de 4 juegos en vivo...';
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
                    body: JSON.stringify({ count: 4 })
                });
                const data = await res.json();
                if (data.success) {
                    this.dripNotice = data.message || '¡Tanda de 4 juegos publicada con éxito!';
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
            if (this.provider === 'romspedia' && this.consoleSlug === 'all') {
                this.consoleSlug = 'psp';
            } else if (this.provider === 'romsemu') {
                this.consoleSlug = 'nintendo-switch';
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
