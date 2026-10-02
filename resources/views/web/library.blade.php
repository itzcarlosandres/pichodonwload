@extends('layouts.web')

@section('title', 'Biblioteca de Videojuegos, Emuladores y BIOS — ' . \App\Models\Setting::get('site_name', 'ROMHUB'))
@section('meta_description', 'Explora la biblioteca unificada de ROMHUB: todos los videojuegos retro recientes ordenados por fecha, emuladores oficiales estables y archivos BIOS originales verificados.')

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-6 sm:py-8 space-y-6 sm:space-y-8"
      x-data="{
          activeTab: '{{ $currentTab }}',
          setTab(t) {
              this.activeTab = t;
              const url = new URL(window.location.href);
              url.searchParams.set('tab', t);
              window.history.replaceState({}, '', url);
              this.$nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
          },
          // Sub-filters for Emulators
          emulatorPlatform: 'all',
          emulatorSearch: '',
          // Sub-filters for BIOS
          biosCategory: 'all',
          biosSearch: ''
      }">

    <!-- Breadcrumbs -->
    <nav class="flex items-center gap-2 text-xs font-mono text-gray-500">
        <a href="{{ route('home') }}" class="hover:text-[#CE2D2D] transition-colors font-medium">INICIO</a>
        <span class="text-gray-400">/</span>
        <span class="text-[#CE2D2D] font-bold uppercase tracking-wider">BIBLIOTECA & CATÁLOGO</span>
    </nav>

    <!-- Header Section -->
    <section class="space-y-4">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#CE2D2D] text-white text-xs font-mono font-bold shadow-sm">
            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
            <span>Centro de Preservación Digital • Catálogo Completo Unificado</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-[#E5E0D8] dark:border-[#27272A] pb-6">
            <div class="space-y-2 max-w-3xl">
                <h1 class="text-3xl sm:text-5xl font-black text-[#18181B] dark:text-white tracking-tight font-sans">
                    Biblioteca & Recursos
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 font-sans leading-relaxed">
                    Accede a todo el ecosistema de preservación en un solo lugar: <strong>videojuegos retro ordenados cronológicamente</strong>, <strong>emuladores oficiales optimizados</strong> para múltiples plataformas y <strong>archivos BIOS de arranque de sistema</strong> verificados para emulación 1:1.
                </p>
            </div>

            <!-- Quick Stats Counters -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <div class="px-3.5 py-2 rounded-xl bg-white dark:bg-[#18181B] border border-[#DDD6CB] dark:border-[#27272A] text-center shadow-xs">
                    <span class="text-[10px] font-mono uppercase text-gray-500 block">Juegos</span>
                    <span class="text-sm sm:text-base font-black text-[#CE2D2D] font-mono">{{ number_format($totalGamesCount) }}</span>
                </div>
                <div class="px-3.5 py-2 rounded-xl bg-white dark:bg-[#18181B] border border-[#DDD6CB] dark:border-[#27272A] text-center shadow-xs">
                    <span class="text-[10px] font-mono uppercase text-gray-500 block">Emuladores</span>
                    <span class="text-sm sm:text-base font-black text-[#18181B] dark:text-white font-mono">{{ count($emulators) }}</span>
                </div>
                <div class="px-3.5 py-2 rounded-xl bg-white dark:bg-[#18181B] border border-[#DDD6CB] dark:border-[#27272A] text-center shadow-xs">
                    <span class="text-[10px] font-mono uppercase text-gray-500 block">BIOS Packs</span>
                    <span class="text-sm sm:text-base font-black text-[#18181B] dark:text-white font-mono">{{ count($biosList) }}</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Master Navigation Tabs Bar -->
    <div class="flex items-center gap-1.5 sm:gap-2 p-1.5 rounded-2xl bg-[#EDE7DE] dark:bg-[#131317] border border-[#DDD6CB] dark:border-[#27272A] shadow-inner overflow-x-auto no-scrollbar">
        <!-- Tab 1: ROMs & Juegos -->
        <button type="button" 
                @click="setTab('games')" 
                :class="activeTab === 'games' ? 'bg-[#CE2D2D] text-white shadow-md font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-white/60 dark:hover:bg-[#202025] font-semibold'"
                class="flex-1 min-w-[130px] sm:min-w-0 py-2.5 px-3 sm:px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
            <i data-lucide="gamepad-2" class="w-4 h-4"></i>
            <span class="whitespace-nowrap">ROMs & Juegos</span>
            <span :class="activeTab === 'games' ? 'bg-black/20 text-white' : 'bg-black/5 dark:bg-white/10 text-gray-600 dark:text-gray-300'" class="px-1.5 py-0.5 rounded-md text-[10px] font-mono font-bold">
                {{ number_format($totalGamesCount) }}
            </span>
        </button>

        <!-- Tab 2: Emuladores Oficiales -->
        <button type="button" 
                @click="setTab('emulators')" 
                :class="activeTab === 'emulators' ? 'bg-[#CE2D2D] text-white shadow-md font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-white/60 dark:hover:bg-[#202025] font-semibold'"
                class="flex-1 min-w-[130px] sm:min-w-0 py-2.5 px-3 sm:px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
            <i data-lucide="cpu" class="w-4 h-4"></i>
            <span class="whitespace-nowrap">Emuladores</span>
            <span :class="activeTab === 'emulators' ? 'bg-black/20 text-white' : 'bg-black/5 dark:bg-white/10 text-gray-600 dark:text-gray-300'" class="px-1.5 py-0.5 rounded-md text-[10px] font-mono font-bold">
                {{ count($emulators) }}
            </span>
        </button>

        <!-- Tab 3: BIOS & Firmwares -->
        <button type="button" 
                @click="setTab('bios')" 
                :class="activeTab === 'bios' ? 'bg-[#CE2D2D] text-white shadow-md font-bold' : 'text-gray-700 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-white/60 dark:hover:bg-[#202025] font-semibold'"
                class="flex-1 min-w-[130px] sm:min-w-0 py-2.5 px-3 sm:px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
            <i data-lucide="binary" class="w-4 h-4"></i>
            <span class="whitespace-nowrap">BIOS & Firmware</span>
            <span :class="activeTab === 'bios' ? 'bg-black/20 text-white' : 'bg-black/5 dark:bg-white/10 text-gray-600 dark:text-gray-300'" class="px-1.5 py-0.5 rounded-md text-[10px] font-mono font-bold">
                {{ count($biosList) }}
            </span>
        </button>
    </div>

    <!-- ================================================================= -->
    <!-- TAB 1: ROMS & JUEGOS RECIENTES                                    -->
    <!-- ================================================================= -->
    <div x-show="activeTab === 'games'" class="space-y-6">
        
        <!-- Controls & Filters Container -->
        <div class="bg-white dark:bg-[#18181B] border border-[#DDD6CB] dark:border-[#27272A] rounded-2xl p-4 sm:p-5 shadow-xs space-y-4">
            <!-- Search & Sort Row -->
            <form action="{{ route('library.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                <input type="hidden" name="tab" value="games">
                @if($selectedConsole)
                    <input type="hidden" name="console" value="{{ $selectedConsole }}">
                @endif

                <!-- Search Input -->
                <div class="relative w-full sm:flex-1">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           name="q" 
                           value="{{ $searchQuery }}" 
                           placeholder="Buscar por título de juego, desarrollador o saga en la biblioteca..." 
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#FAF7F2] dark:bg-[#202025] border border-[#DDD6CB] dark:border-[#2E2E35] focus:border-[#CE2D2D] text-xs font-sans text-[#18181B] dark:text-white outline-none">
                </div>

                <!-- Sort Selector -->
                <div class="w-full sm:w-auto flex items-center gap-2">
                    <div class="relative w-full sm:w-52 shrink-0">
                        <select name="sort" 
                                onchange="this.form.submit()" 
                                class="w-full px-3 py-2.5 rounded-xl bg-[#FAF7F2] dark:bg-[#202025] border border-[#DDD6CB] dark:border-[#2E2E35] focus:border-[#CE2D2D] text-xs font-sans text-[#18181B] dark:text-white outline-none cursor-pointer">
                            <option value="recent" {{ $selectedSort === 'recent' ? 'selected' : '' }}>★ Más Recientes</option>
                            <option value="downloads" {{ $selectedSort === 'downloads' ? 'selected' : '' }}>🔥 Más Descargados</option>
                            <option value="rating" {{ $selectedSort === 'rating' ? 'selected' : '' }}>🏆 Mejor Valorados</option>
                            <option value="name_asc" {{ $selectedSort === 'name_asc' ? 'selected' : '' }}>Alfabeto (A → Z)</option>
                            <option value="name_desc" {{ $selectedSort === 'name_desc' ? 'selected' : '' }}>Alfabeto (Z → A)</option>
                            <option value="oldest" {{ $selectedSort === 'oldest' ? 'selected' : '' }}>Primeros Añadidos</option>
                        </select>
                    </div>

                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white text-xs font-bold uppercase font-mono shadow-sm transition-colors shrink-0">
                        Filtrar
                    </button>

                    @if(!empty($searchQuery) || !empty($selectedConsole) || $selectedSort !== 'recent')
                        <a href="{{ route('library.index', ['tab' => 'games']) }}" class="px-3 py-2.5 rounded-xl bg-gray-200 dark:bg-[#272730] hover:bg-gray-300 text-gray-700 dark:text-gray-300 text-xs font-mono transition-colors shrink-0" title="Limpiar Filtros">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>

            <!-- Console Pill Chips Bar -->
            <div class="pt-3 border-t border-[#E5E0D8] dark:border-[#27272A]">
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar text-xs font-mono">
                    <!-- All Consoles Pill -->
                    <a href="{{ route('library.index', array_merge(request()->except('page'), ['tab' => 'games', 'console' => ''])) }}" 
                       class="px-3 py-1.5 rounded-xl border whitespace-nowrap transition-all flex items-center gap-1.5 shrink-0 {{ empty($selectedConsole) ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-xs' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 hover:text-black dark:hover:text-white border-[#DDD6CB] dark:border-[#2E2E35]' }}">
                        <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                        <span>Todas las Consolas</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded font-bold {{ empty($selectedConsole) ? 'bg-black/20 text-white' : 'bg-white dark:bg-[#18181B] text-gray-500' }}">{{ $totalGamesCount }}</span>
                    </a>

                    @foreach($consoles as $con)
                        @php $isActive = $selectedConsole === $con->slug; @endphp
                        <a href="{{ route('library.index', array_merge(request()->except('page'), ['tab' => 'games', 'console' => $con->slug])) }}" 
                           class="px-3 py-1.5 rounded-xl border whitespace-nowrap transition-all flex items-center gap-1.5 shrink-0 {{ $isActive ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-xs' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 hover:text-black dark:hover:text-white border-[#DDD6CB] dark:border-[#2E2E35]' }}">
                            <span>{{ $con->short_name ?: $con->name }}</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded font-bold {{ $isActive ? 'bg-black/20 text-white' : 'bg-white dark:bg-[#18181B] text-gray-500' }}">{{ $con->games_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Games Grid -->
        @if($games->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                @foreach($games as $game)
                    <div class="group relative flex flex-col justify-between space-y-2 cursor-pointer">
                        <!-- Floating Cover -->
                        <div class="relative aspect-[3/4.2] rounded-2xl overflow-hidden border border-[#E5E0D8] dark:border-white/[0.08] hover:border-[#CE2D2D]/60 dark:hover:border-[#CE2D2D]/60 shadow-md hover:shadow-2xl hover:shadow-red-600/25 transition-all duration-400 group-hover:-translate-y-1.5 bg-[#FAF7F2] dark:bg-[#090C12]">
                            <a href="{{ route('game.show', $game->slug) }}" class="block w-full h-full">
                                <img src="{{ $game->cover_thumb_url ?: $game->cover_url }}" 
                                     alt="{{ $game->title }}" 
                                     loading="lazy"
                                     onerror="this.onerror=null; this.src='{{ asset('images/placeholder-cover.svg') }}';"
                                     class="w-full h-full object-cover object-center group-hover:scale-106 transition-transform duration-500 ease-out">
                            </a>

                            <!-- Subtle Vignette -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent pointer-events-none"></div>

                            <!-- Console Tag Top-Left -->
                            <div class="absolute top-2.5 left-2.5 z-10 pointer-events-none">
                                <span class="px-2 py-0.5 rounded-lg bg-black/65 backdrop-blur-md border border-white/15 text-[9px] font-mono font-bold text-white shadow-sm">
                                    {{ $game->console->short_name ?: $game->console->name }}
                                </span>
                            </div>

                            <!-- Badge Top-Right -->
                            @if($game->badges && $game->badges->isNotEmpty())
                                @php $topBadge = $game->badges->first(); @endphp
                                <div class="absolute top-2.5 right-2.5 z-10 max-w-[65%] pointer-events-none">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] sm:text-[9.5px] font-mono font-bold shadow-md truncate backdrop-blur-md transition-transform group-hover:scale-105"
                                          style="background-color: {{ $topBadge->bg_color ?: '#881337' }}; color: {{ $topBadge->text_color ?: '#fff0f2' }}; border: 1px solid {{ $topBadge->border_color ?: '#e11d48' }}">
                                        @if($topBadge->icon)
                                            <i data-lucide="{{ $topBadge->icon }}" class="w-2.5 h-2.5 sm:w-3 sm:h-3 shrink-0"></i>
                                        @else
                                            <i data-lucide="award" class="w-2.5 h-2.5 sm:w-3 sm:h-3 shrink-0"></i>
                                        @endif
                                        <span class="truncate">{{ $topBadge->name }}</span>
                                    </span>
                                </div>
                            @endif

                            <!-- Size Pill Bottom-Left -->
                            <div class="absolute bottom-2.5 left-2.5 z-10 px-2 py-0.5 rounded-md bg-black/75 backdrop-blur-md text-[10px] font-mono text-gray-200 border border-white/10 group-hover:opacity-0 transition-opacity duration-200 pointer-events-none">
                                {{ $game->formatted_size }}
                            </div>

                            <!-- Rating Pill Bottom-Right -->
                            <div class="absolute bottom-2.5 right-2.5 z-10 px-2 py-0.5 rounded-md bg-black/75 backdrop-blur-md text-[10px] font-mono text-amber-400 font-bold border border-white/10 flex items-center gap-1 group-hover:opacity-0 transition-opacity duration-200 pointer-events-none">
                                <span>★</span>
                                <span>{{ number_format($game->rating_average ?: 5.0, 1) }}</span>
                            </div>

                            <!-- Card Shine Ray -->
                            <div class="card-shine-ray"></div>
                        </div>

                        <!-- Game Info -->
                        <div class="space-y-1 px-1">
                            <h3 class="text-xs sm:text-sm font-black text-[#18181B] dark:text-[#F4F4F5] line-clamp-1 leading-snug font-sans group-hover:text-[#CE2D2D] dark:group-hover:text-[#EF4444] transition-colors">
                                <a href="{{ route('game.show', $game->slug) }}" class="card-title-line">
                                    {{ $game->title }}
                                </a>
                            </h3>

                            <div class="flex items-center justify-between text-[11px] font-mono text-gray-500 dark:text-[#94A3B8]">
                                <span class="truncate">{{ $game->developer ?: 'Retro Classic' }}</span>
                                <span class="flex items-center gap-1 shrink-0 ml-1 text-gray-600 dark:text-gray-400">
                                    <i data-lucide="download" class="w-3 h-3 text-[#CE2D2D]"></i>
                                    {{ number_format($game->download_count) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination Bar -->
            <div class="pt-6 border-t border-[#E5E0D8] dark:border-[#27272A]">
                {{ $games->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="p-12 text-center bg-white dark:bg-[#18181B] border-2 border-dashed border-[#DDD6CB] dark:border-[#27272A] rounded-2xl space-y-3">
                <i data-lucide="folder-search" class="w-10 h-10 text-gray-400 mx-auto"></i>
                <h3 class="text-base font-bold text-[#18181B] dark:text-white font-sans">No se encontraron títulos con los criterios indicados</h3>
                <p class="text-xs text-gray-500 font-mono">Prueba cambiando la consola seleccionada o utilizando otros términos de búsqueda.</p>
                <div class="pt-2">
                    <a href="{{ route('library.index', ['tab' => 'games']) }}" class="px-4 py-2 rounded-xl bg-[#CE2D2D] text-white text-xs font-bold font-mono inline-flex items-center gap-2">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        <span>Ver Todo el Catálogo</span>
                    </a>
                </div>
            </div>
        @endif

    </div>

    <!-- ================================================================= -->
    <!-- TAB 2: DIRECTORIO DE EMULADORES OFICIALES                         -->
    <!-- ================================================================= -->
    <div x-show="activeTab === 'emulators'" class="space-y-6">
        
        <!-- Emulators Sub-Bar -->
        <div class="bg-white dark:bg-[#18181B] border border-[#DDD6CB] dark:border-[#27272A] rounded-2xl p-4 sm:p-5 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <!-- Search Filter -->
                <div class="relative w-full sm:flex-1">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           x-model="emulatorSearch" 
                           placeholder="Buscar emulador (ej: PCSX2, Dolphin, DuckStation, Citra, Yuzu)..." 
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#FAF7F2] dark:bg-[#202025] border border-[#DDD6CB] dark:border-[#2E2E35] focus:border-[#CE2D2D] text-xs font-sans text-[#18181B] dark:text-white outline-none">
                </div>

                <!-- Platform Filters -->
                <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto no-scrollbar text-xs font-mono">
                    <button type="button" @click="emulatorPlatform = 'all'" 
                            :class="emulatorPlatform === 'all' ? 'bg-[#CE2D2D] text-white font-bold' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 border-[#DDD6CB] dark:border-[#2E2E35]'" 
                            class="px-3 py-1.5 rounded-xl border transition-all shrink-0">
                        Todos
                    </button>
                    <button type="button" @click="emulatorPlatform = 'windows'" 
                            :class="emulatorPlatform === 'windows' ? 'bg-[#CE2D2D] text-white font-bold' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 border-[#DDD6CB] dark:border-[#2E2E35]'" 
                            class="px-3 py-1.5 rounded-xl border transition-all shrink-0">
                        Windows
                    </button>
                    <button type="button" @click="emulatorPlatform = 'android'" 
                            :class="emulatorPlatform === 'android' ? 'bg-[#CE2D2D] text-white font-bold' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 border-[#DDD6CB] dark:border-[#2E2E35]'" 
                            class="px-3 py-1.5 rounded-xl border transition-all shrink-0">
                        Android
                    </button>
                    <button type="button" @click="emulatorPlatform = 'linux'" 
                            :class="emulatorPlatform === 'linux' ? 'bg-[#CE2D2D] text-white font-bold' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 border-[#DDD6CB] dark:border-[#2E2E35]'" 
                            class="px-3 py-1.5 rounded-xl border transition-all shrink-0">
                        Linux / Steam Deck
                    </button>
                    <button type="button" @click="emulatorPlatform = 'macos'" 
                            :class="emulatorPlatform === 'macos' ? 'bg-[#CE2D2D] text-white font-bold' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 border-[#DDD6CB] dark:border-[#2E2E35]'" 
                            class="px-3 py-1.5 rounded-xl border transition-all shrink-0">
                        macOS
                    </button>
                </div>
            </div>
        </div>

        <!-- Emulators Card Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach($emulators as $emu)
                @php
                    $platformsList = is_array($emu->platforms) ? $emu->platforms : [];
                    $featuresList = is_array($emu->features) ? $emu->features : [];
                    $platformString = strtolower(implode(' ', $platformsList));
                @endphp
                <div x-show="(emulatorPlatform === 'all' || '{{ $platformString }}'.includes(emulatorPlatform)) && 
                             (!emulatorSearch || '{{ strtolower($emu->name . ' ' . $emu->system . ' ' . $emu->description) }}'.includes(emulatorSearch.toLowerCase()))"
                     class="bg-white dark:bg-[#18181B] border border-[#DDD6CB] dark:border-[#27272A] hover:border-[#CE2D2D] dark:hover:border-[#CE2D2D] rounded-2xl p-5 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                    
                    <div class="space-y-3.5">
                        <!-- Top Bar: Icon + System Badge -->
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-[#FDF2F2] dark:bg-[#2B1616] border border-[#FCA5A5] dark:border-[#632323] text-[#CE2D2D] flex items-center justify-center font-bold shadow-xs shrink-0 group-hover:scale-105 transition-transform">
                                    @if($emu->icon)
                                        <i data-lucide="{{ $emu->icon }}" class="w-6 h-6"></i>
                                    @else
                                        <i data-lucide="cpu" class="w-6 h-6"></i>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-base font-black text-[#18181B] dark:text-white font-sans group-hover:text-[#CE2D2D] transition-colors leading-tight">
                                        {{ $emu->name }}
                                    </h3>
                                    <span class="text-[11px] font-mono text-gray-500 dark:text-gray-400">
                                        v{{ $emu->version ?: 'Estable' }}
                                    </span>
                                </div>
                            </div>

                            <span class="px-2.5 py-1 rounded-lg bg-[#FAF7F2] dark:bg-[#202025] border border-[#DDD6CB] dark:border-[#2E2E35] text-[10px] font-mono font-bold text-[#CE2D2D] uppercase tracking-wider shrink-0">
                                {{ $emu->system }}
                            </span>
                        </div>

                        <!-- Description -->
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-sans leading-relaxed line-clamp-3">
                            {{ $emu->description ?: 'Emulador oficial de alto rendimiento verificado para reproducción fiel y gráficos escalados en alta definición.' }}
                        </p>

                        <!-- Features / Highlights Tags -->
                        @if(!empty($featuresList))
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                @foreach(array_slice($featuresList, 0, 3) as $feat)
                                    <span class="px-2 py-0.5 rounded-md bg-[#FAF7F2] dark:bg-[#202025] border border-[#E5E0D8] dark:border-[#27272A] text-[10px] font-mono text-gray-600 dark:text-gray-400">
                                        ✓ {{ $feat }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Supported Platforms Badges -->
                        <div class="flex items-center gap-1 text-[10px] font-mono text-gray-500 pt-1">
                            <span class="font-bold">Soporta:</span>
                            @foreach($platformsList as $plt)
                                <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-[#272730] text-gray-700 dark:text-gray-300 uppercase text-[9px] font-semibold">{{ $plt }}</span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-4 mt-4 border-t border-[#E5E0D8] dark:border-[#27272A] flex items-center gap-2">
                        @if($emu->download_url)
                            <a href="{{ $emu->download_url }}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="flex-1 px-4 py-2.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white text-xs font-bold font-mono uppercase tracking-wider flex items-center justify-center gap-2 shadow-sm transition-colors">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>Descargar</span>
                            </a>
                        @endif

                        @if($emu->website)
                            <a href="{{ $emu->website }}" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="p-2.5 rounded-xl bg-[#FAF7F2] dark:bg-[#202025] hover:bg-white dark:hover:bg-[#272730] border border-[#DDD6CB] dark:border-[#2E2E35] text-gray-700 dark:text-gray-300 hover:text-[#CE2D2D] transition-colors"
                               title="Sitio Web Oficial">
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    <!-- ================================================================= -->
    <!-- TAB 3: BÓVEDA DE BIOS & FIRMWARES                                 -->
    <!-- ================================================================= -->
    <div x-show="activeTab === 'bios'" class="space-y-6">
        
        <!-- BIOS Sub-Bar -->
        <div class="bg-white dark:bg-[#18181B] border border-[#DDD6CB] dark:border-[#27272A] rounded-2xl p-4 sm:p-5 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <!-- Search Input -->
                <div class="relative w-full sm:flex-1">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           x-model="biosSearch" 
                           placeholder="Buscar pack de BIOS por consola (ej: PS2, PS1, Sega, Dreamcast, GameCube, Xbox)..." 
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#FAF7F2] dark:bg-[#202025] border border-[#DDD6CB] dark:border-[#2E2E35] focus:border-[#CE2D2D] text-xs font-sans text-[#18181B] dark:text-white outline-none">
                </div>

                <!-- Ecosystem Chips -->
                <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto no-scrollbar text-xs font-mono">
                    <button type="button" @click="biosCategory = 'all'" 
                            :class="biosCategory === 'all' ? 'bg-[#CE2D2D] text-white font-bold' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 border-[#DDD6CB] dark:border-[#2E2E35]'" 
                            class="px-3 py-1.5 rounded-xl border transition-all shrink-0">
                        Todos ({{ count($biosList) }})
                    </button>
                    <button type="button" @click="biosCategory = 'playstation'" 
                            :class="biosCategory === 'playstation' ? 'bg-[#CE2D2D] text-white font-bold' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 border-[#DDD6CB] dark:border-[#2E2E35]'" 
                            class="px-3 py-1.5 rounded-xl border transition-all shrink-0">
                        PlayStation
                    </button>
                    <button type="button" @click="biosCategory = 'nintendo'" 
                            :class="biosCategory === 'nintendo' ? 'bg-[#CE2D2D] text-white font-bold' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 border-[#DDD6CB] dark:border-[#2E2E35]'" 
                            class="px-3 py-1.5 rounded-xl border transition-all shrink-0">
                        Nintendo
                    </button>
                    <button type="button" @click="biosCategory = 'sega'" 
                            :class="biosCategory === 'sega' ? 'bg-[#CE2D2D] text-white font-bold' : 'bg-[#FAF7F2] dark:bg-[#202025] text-gray-700 dark:text-gray-300 border-[#DDD6CB] dark:border-[#2E2E35]'" 
                            class="px-3 py-1.5 rounded-xl border transition-all shrink-0">
                        Sega
                    </button>
                </div>
            </div>
        </div>

        <!-- BIOS Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach($biosList as $bios)
                @php
                    $systemLower = strtolower($bios->system . ' ' . ($bios->console->name ?? ''));
                @endphp
                <div x-show="(biosCategory === 'all' || 
                             (biosCategory === 'playstation' && ('{{ $systemLower }}'.includes('playstation') || '{{ $systemLower }}'.includes('ps1') || '{{ $systemLower }}'.includes('ps2') || '{{ $systemLower }}'.includes('ps3') || '{{ $systemLower }}'.includes('psp'))) || 
                             (biosCategory === 'nintendo' && ('{{ $systemLower }}'.includes('nintendo') || '{{ $systemLower }}'.includes('gamecube') || '{{ $systemLower }}'.includes('gba') || '{{ $systemLower }}'.includes('ds') || '{{ $systemLower }}'.includes('switch'))) || 
                             (biosCategory === 'sega' && ('{{ $systemLower }}'.includes('sega') || '{{ $systemLower }}'.includes('dreamcast') || '{{ $systemLower }}'.includes('saturn')))) && 
                             (!biosSearch || '{{ strtolower($bios->system . ' ' . $bios->files . ' ' . $bios->emulator . ' ' . $bios->description) }}'.includes(biosSearch.toLowerCase()))"
                     class="bg-white dark:bg-[#18181B] border border-[#DDD6CB] dark:border-[#27272A] hover:border-[#CE2D2D] dark:hover:border-[#CE2D2D] rounded-2xl p-5 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                    
                    <div class="space-y-3.5">
                        <!-- Top Line -->
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-[#FDF2F2] dark:bg-[#2B1616] border border-[#FCA5A5] dark:border-[#632323] text-[#CE2D2D] flex items-center justify-center font-bold shadow-xs shrink-0 group-hover:scale-105 transition-transform">
                                    <i data-lucide="binary" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-black text-[#18181B] dark:text-white font-sans group-hover:text-[#CE2D2D] transition-colors leading-tight">
                                        {{ $bios->system }}
                                    </h3>
                                    <span class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1 mt-0.5">
                                        <i data-lucide="shield-check" class="w-3 h-3"></i> Dump Limpio 1:1 Verificado
                                    </span>
                                </div>
                            </div>

                            <span class="px-2 py-0.5 rounded-md bg-[#FAF7F2] dark:bg-[#202025] border border-[#DDD6CB] dark:border-[#2E2E35] text-[10px] font-mono font-bold text-gray-700 dark:text-gray-300">
                                {{ $bios->size ?: 'ZIP' }}
                            </span>
                        </div>

                        <!-- Description -->
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-sans leading-relaxed line-clamp-3">
                            {{ $bios->description ?: 'Pack de arranque de sistema original requerido para emulación exacta en emuladores compatibles.' }}
                        </p>

                        <!-- Tech Meta: Filenames & Emulator -->
                        <div class="space-y-1.5 p-3 rounded-xl bg-[#FAF7F2] dark:bg-[#202025] border border-[#E5E0D8] dark:border-[#27272A] font-mono text-[11px]">
                            @if($bios->files)
                                <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                                    <span>Archivos:</span>
                                    <span class="text-[#18181B] dark:text-white font-bold truncate max-w-[60%]">{{ $bios->files }}</span>
                                </div>
                            @endif

                            @if($bios->emulator)
                                <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                                    <span>Emulador:</span>
                                    <span class="text-[#CE2D2D] font-bold">{{ $bios->emulator }}</span>
                                </div>
                            @endif

                            @if($bios->md5)
                                <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                                    <span>MD5:</span>
                                    <span class="text-gray-500 font-mono text-[10px] truncate max-w-[60%]">{{ $bios->md5 }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Direct Download Button -->
                    <div class="pt-4 mt-4 border-t border-[#E5E0D8] dark:border-[#27272A]">
                        <a href="{{ route('bios.download', $bios->id) }}" 
                           class="w-full px-4 py-2.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white text-xs font-bold font-mono uppercase tracking-wider flex items-center justify-center gap-2 shadow-sm transition-colors">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            <span>Descargar Pack de BIOS</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

</main>
@endsection
