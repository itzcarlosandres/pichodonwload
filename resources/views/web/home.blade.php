@extends('layouts.web')

@section('title', \App\Models\Setting::get('seo_meta_title', \App\Models\Setting::get('site_name', 'ROMHUB') . ' — Retro ROMs & Emulators for Every Classic Console'))

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-8 space-y-14">

    <!-- 1. HERO SECTION & SEARCH (Retro Clean Aesthetic RomsRetro) -->
    <section class="text-center space-y-6 max-w-3xl mx-auto pt-4">
        
        <!-- Retro Red Announcement Pill -->
        @if($badge = \App\Models\Setting::get('home_hero_badge', 'ROM VAULT • 20 RETRO SYSTEMS • 100% CLEAN DUMPS'))
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#CE2D2D] text-white text-xs font-mono font-bold shadow-md shadow-red-500/20">
            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
            <span>{{ $badge }}</span>
        </div>
        @endif

        <!-- Hero Headline with Red Word Accent -->
        <div class="space-y-3">
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-[#18181B] tracking-tight font-sans leading-[1.15]">
                {{ \App\Models\Setting::get('home_hero_title_prefix', 'Retro ROMs & Emulators for') }} <br class="hidden sm:inline">
                <span class="text-[#CE2D2D]">{{ \App\Models\Setting::get('home_hero_title_highlight', 'every classic console.') }}</span>
            </h1>
            @if($description = \App\Models\Setting::get('home_hero_description', 'Descarga videojuegos retro verificados (No-Intro / Redump), archivos BIOS y emuladores para PlayStation, Nintendo, Sega, Xbox y más de 15 sistemas clásicos.'))
            <p class="text-xs sm:text-base text-gray-600 max-w-2xl mx-auto font-sans leading-relaxed">
                {{ $description }}
            </p>
            @endif
        </div>

        <!-- Center Retro Pill Search Form with Dark Border & Red Button & LIVE AUTOCOMPLETE -->
        <div class="relative max-w-2xl mx-auto text-left"
             x-data="{
                 query: '',
                 results: [],
                 loading: false,
                 open: false,
                 timeout: null,
                 onInput() {
                     clearTimeout(this.timeout);
                     if (this.query.trim().length < 2) {
                         this.results = [];
                         this.open = false;
                         this.loading = false;
                         return;
                     }
                     this.loading = true;
                     this.open = true;
                     this.timeout = setTimeout(() => {
                         fetch(`{{ route('search.live') }}?q=${encodeURIComponent(this.query)}`)
                             .then(res => res.json())
                             .then(data => {
                                 this.results = data;
                                 this.loading = false;
                                 $nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
                             })
                             .catch(() => {
                                 this.loading = false;
                             });
                     }, 250);
                 },
                 onFocus() {
                     if (this.query.trim().length >= 2 && this.results.length > 0) {
                         this.open = true;
                         $nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
                     }
                 },
                 close() {
                     this.open = false;
                 }
             }"
             @click.outside="close()"
             @keydown.escape.window="close()">
            
            <form action="{{ route('search') }}" method="GET" class="p-1.5 sm:p-2 rounded-2xl bg-white border-2 border-[#1E1E1E] shadow-[0_10px_30px_rgba(0,0,0,0.06)] flex flex-col sm:flex-row items-center gap-2 transition-all relative z-30">
                
                <!-- Search Text Input -->
                <div class="relative flex-1 w-full flex items-center pl-3">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 shrink-0"></i>
                    <input 
                        type="text" 
                        name="q" 
                        x-model="query"
                        @input="onInput()"
                        @focus="onFocus()"
                        autocomplete="off"
                        placeholder="{{ \App\Models\Setting::get('home_hero_search_placeholder', 'Buscar juego, consola o BIOS...') }}" 
                        class="w-full bg-transparent border-0 px-3 py-2 text-xs sm:text-sm text-[#18181B] placeholder-gray-400 focus:outline-none font-sans font-medium"
                    >
                    <!-- Clear button & Spinner -->
                    <div class="flex items-center pr-2 gap-1.5">
                        <template x-if="loading">
                            <div class="w-4 h-4 border-2 border-[#CE2D2D] border-t-transparent rounded-full animate-spin"></div>
                        </template>
                        <template x-if="query.length > 0 && !loading">
                            <button type="button" @click="query = ''; results = []; open = false" class="p-1 text-gray-400 hover:text-black">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Red Search Action Button -->
                <button type="submit" class="w-full sm:w-auto px-7 py-3 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-black text-xs uppercase tracking-wider font-sans flex items-center justify-center gap-2 transition-all shadow-md shadow-red-500/25 shrink-0 cursor-pointer">
                    <span>{{ \App\Models\Setting::get('home_hero_search_button', 'Buscar') }}</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </button>
            </form>

            <!-- LIVE SEARCH RESULTS DROPDOWN -->
            <div x-show="open" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-2 scale-98"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-2 scale-98"
                 class="absolute left-0 right-0 top-full mt-2 bg-white border-2 border-[#1E1E1E] rounded-2xl shadow-[0_20px_40px_rgba(0,0,0,0.15)] overflow-hidden z-50">
                
                <!-- Top header banner in dropdown -->
                <div class="px-4 py-2.5 bg-[#FAF7F2] border-b border-[#E5E0D8] flex items-center justify-between text-xs font-mono">
                    <span class="font-bold text-[#18181B] flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#CE2D2D] animate-pulse"></span>
                        <span x-text="loading ? 'Buscando títulos...' : (results.length > 0 ? 'Resultados instantáneos (' + results.length + ')' : 'Sin resultados')"></span>
                    </span>
                    <span class="text-gray-500 text-[11px] font-sans" x-show="results.length > 0">Presiona Enter para ver todos</span>
                </div>

                <!-- Results list -->
                <div class="max-h-[360px] overflow-y-auto divide-y divide-[#E5E0D8]">
                    <template x-for="item in results" :key="item.id">
                        <a :href="item.url" 
                           class="flex items-center gap-3.5 p-3 hover:bg-[#FAF7F2] transition-colors group">
                            <!-- Cover thumb -->
                            <div class="w-11 h-14 bg-[#EDE7DE] rounded-lg border border-[#DDD6CB] overflow-hidden shrink-0 flex items-center justify-center">
                                <img :src="item.cover_url" :alt="item.title" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80';" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#F5EFE6] text-[#CE2D2D] border border-[#DDD6CB]" x-text="item.console"></span>
                                    <template x-if="item.region">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-gray-100 text-gray-600 uppercase" x-text="item.region"></span>
                                    </template>
                                </div>
                                <h4 class="text-xs sm:text-sm font-bold text-[#18181B] group-hover:text-[#CE2D2D] truncate transition-colors font-sans" x-text="item.title"></h4>
                                <div class="flex items-center gap-3 text-[11px] font-mono text-gray-500 mt-1">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="hard-drive" class="w-3 h-3 text-gray-400"></i>
                                        <span x-text="item.formatted_size"></span>
                                    </span>
                                    <template x-if="item.rating">
                                        <span class="flex items-center gap-1 text-amber-600 font-bold">
                                            <i data-lucide="star" class="w-3 h-3 fill-amber-400 text-amber-400"></i>
                                            <span x-text="item.rating"></span>
                                        </span>
                                    </template>
                                    <template x-if="item.downloads && item.downloads !== '0'">
                                        <span class="hidden sm:flex items-center gap-1">
                                            <i data-lucide="download" class="w-3 h-3 text-gray-400"></i>
                                            <span x-text="item.downloads"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- Action button icon -->
                            <div class="w-8 h-8 rounded-lg bg-[#FAF7F2] group-hover:bg-[#CE2D2D] group-hover:text-white border border-[#DDD6CB] group-hover:border-[#CE2D2D] flex items-center justify-center text-gray-500 transition-all shrink-0">
                                <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform"></i>
                            </div>
                        </a>
                    </template>

                    <!-- Empty state -->
                    <template x-if="!loading && results.length === 0 && query.trim().length >= 2">
                        <div class="p-6 text-center space-y-2">
                            <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto">
                                <i data-lucide="search-x" class="w-5 h-5"></i>
                            </div>
                            <p class="text-xs font-bold text-[#18181B] font-sans">No encontramos títulos para "<span x-text="query"></span>"</p>
                            <p class="text-[11px] text-gray-500 font-sans">Prueba con otra palabra clave o pulsa Enter para buscar en todo el catálogo.</p>
                        </div>
                    </template>
                </div>

                <!-- Footer with Explore full link -->
                <div class="p-2.5 bg-[#FAF7F2] border-t border-[#E5E0D8] text-center">
                    <a :href="'{{ route('search') }}?q=' + encodeURIComponent(query)" 
                       class="inline-flex items-center gap-1.5 text-xs font-mono font-bold text-[#CE2D2D] hover:underline">
                        <span>Ver todos los resultados en el Explorador</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

            </div>

        </div>

    </section>

    <!-- 2. CONSOLES SECTION (Retro Card Grid) -->
    <section class="space-y-4">
        <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-[#CE2D2D] rounded-sm shrink-0"></span>
                <h2 class="text-base sm:text-lg font-black text-[#18181B] font-sans tracking-tight uppercase">
                    Consoles
                </h2>
            </div>
            <a href="{{ route('consoles.index') }}" class="px-3 py-1 rounded-full border border-[#1E1E1E] bg-white hover:bg-[#FAF7F2] text-xs font-mono font-bold text-[#18181B] transition-colors flex items-center gap-1">
                <span>Ver las {{ $totalConsoles }} consolas</span>
                <i data-lucide="arrow-right" class="w-3 h-3 text-[#CE2D2D]"></i>
            </a>
        </div>

        <!-- 20 Consoles Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7 gap-2.5 sm:gap-3.5">
            @php
                $consoleIconMap = [
                    'playstation-2' => 'gamepad-2',
                    'playstation-1' => 'disc',
                    'playstation-portable' => 'smartphone',
                    'playstation-vita' => 'tablet',
                    'playstation-3' => 'hard-drive',
                    'nintendo-switch' => 'switch-camera',
                    'nintendo-gamecube' => 'box',
                    'nintendo-wii' => 'wand-2',
                    'nintendo-wii-u' => 'tv',
                    'nintendo-ds' => 'split',
                    'nintendo-3ds' => 'layers',
                    'game-boy-advance' => 'cpu',
                    'game-boy-color' => 'palette',
                    'nintendo-64' => 'dices',
                    'super-nintendo' => 'gamepad',
                    'nes' => 'tv-2',
                    'xbox-360' => 'circle-dot',
                    'xbox-original' => 'box-select',
                    'sega-dreamcast' => 'disc-3',
                    'sega-genesis' => 'radio',
                ];
            @endphp

            @foreach($consoles as $con)
                @php
                    $cIcon = $consoleIconMap[$con->slug] ?? 'gamepad-2';
                @endphp
                <a href="{{ route('consoles.show', $con->slug) }}" 
                   class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-2.5 sm:p-4 text-center hover:-translate-y-1 active:scale-[0.98] transition-all duration-200 shadow-sm hover:shadow-md group block">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 mx-auto rounded-xl bg-[#FAF7F2] border border-[#E5E0D8] group-hover:bg-[#FDF2F2] group-hover:border-[#FCA5A5] flex items-center justify-center transition-colors">
                        <i data-lucide="{{ $cIcon }}" class="w-4.5 h-4.5 sm:w-5 sm:h-5 text-[#18181B] group-hover:text-[#CE2D2D] transition-colors"></i>
                    </div>
                    <p class="text-[11px] sm:text-[13px] font-black text-[#18181B] group-hover:text-[#CE2D2D] transition-colors truncate mt-2 font-sans">
                        {{ $con->short_name ?: $con->name }}
                    </p>
                    <p class="text-[9px] sm:text-[10px] font-mono font-bold text-gray-500 uppercase tracking-wider mt-0.5">
                        {{ $con->games_count }} {{ $con->games_count == 1 ? 'TÍTULO' : 'TÍTULOS' }}
                    </p>
                </a>
            @endforeach
        </div>
    </section>

    <!-- 3. LATEST GAMES SECTION (Retro Poster Grid) -->
    <section id="novedades" class="space-y-4 scroll-mt-24">
        <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-[#CE2D2D] rounded-sm shrink-0"></span>
                <h2 class="text-base sm:text-lg font-black text-[#18181B] font-sans tracking-tight uppercase">
                    {{ \App\Models\Setting::get('home_recent_title', 'Latest Games') }}
                </h2>
            </div>
            <a href="{{ route('search') }}" class="px-3.5 sm:px-4 py-1.5 rounded-full border border-[#1E1E1E] bg-white hover:bg-[#FAF7F2] text-xs font-mono font-bold text-[#18181B] transition-colors flex items-center gap-1.5 shadow-sm">
                <span>Más ROMs</span>
                <i data-lucide="arrow-right" class="w-3 h-3 text-[#CE2D2D]"></i>
            </a>
        </div>

        <!-- Games Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            @forelse($recentGames as $game)
                <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1 hover:shadow-lg relative">
                    
                    <!-- Cover image container -->
                    <div class="aspect-[3/4.1] bg-[#FAF7F2] relative overflow-hidden border-b border-[#E5E0D8]">
                        <a href="{{ route('game.show', $game->slug) }}" class="block w-full h-full">
                            <img src="{{ $game->cover_thumb_url ?: $game->cover_url }}" 
                                 alt="{{ $game->title }}" 
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80';"
                                 class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300">
                        </a>
                        
                        <!-- Top floating console pill -->
                        <div class="absolute top-2 left-2 z-10">
                            <span class="console-card-badge px-2 py-0.5 rounded-md bg-white/95 dark:bg-[#18181B]/95 text-[#18181B] dark:text-white border border-[#1E1E1E] dark:border-[#3F3F46] shadow-sm text-[10px] font-mono font-black backdrop-blur">
                                {{ $game->console->short_name ?: $game->console->name }}
                            </span>
                        </div>

                        <!-- Hover quick action -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-2.5 pointer-events-none">
                            <span class="w-full py-1.5 rounded-lg bg-[#CE2D2D] text-white text-[11px] font-bold font-sans flex items-center justify-center gap-1 shadow-md">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>Ver ROM</span>
                            </span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-3 space-y-1.5 flex-1 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] font-mono font-bold text-gray-500 uppercase block truncate">
                                {{ $game->console->name }}
                            </span>
                            <h3 class="card-game-title text-xs sm:text-[13px] font-black text-[#18181B] group-hover:text-[#CE2D2D] transition-colors line-clamp-1 leading-snug font-sans mt-0.5">
                                <a href="{{ route('game.show', $game->slug) }}">{{ $game->title }}</a>
                            </h3>
                        </div>

                        <!-- Footer info -->
                        <div class="pt-2 border-t border-[#E5E0D8] flex items-center justify-between text-[11px] font-mono">
                            <span class="text-[10px] text-gray-500 font-semibold">{{ $game->formatted_size }}</span>
                            <span class="text-amber-500 font-bold flex items-center gap-0.5">
                                <span>★</span>
                                <span>{{ number_format($game->rating_average, 1) }}</span>
                            </span>
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-span-full p-12 text-center bg-white border-2 border-[#1E1E1E] rounded-2xl">
                    <p class="text-sm font-bold text-gray-600 font-mono">No hay títulos disponibles para esta selección.</p>
                </div>
            @endforelse
        </div>

        <!-- Retro Pagination Row -->
        <div class="flex items-center justify-center gap-2 pt-6">
            <a href="{{ route('search') }}" class="w-9 h-9 rounded-full bg-[#CE2D2D] text-white flex items-center justify-center font-bold text-xs shadow-md shadow-red-500/20">
                1
            </a>
            <a href="{{ route('search', ['page' => 2]) }}" class="w-9 h-9 rounded-full bg-white border border-[#DDD6CB] hover:border-[#1E1E1E] text-[#18181B] flex items-center justify-center font-bold text-xs transition-colors">
                2
            </a>
            <a href="{{ route('search', ['page' => 3]) }}" class="w-9 h-9 rounded-full bg-white border border-[#DDD6CB] hover:border-[#1E1E1E] text-[#18181B] flex items-center justify-center font-bold text-xs transition-colors">
                3
            </a>
            <a href="{{ route('search', ['page' => 2]) }}" class="w-9 h-9 rounded-full bg-white border border-[#DDD6CB] hover:border-[#1E1E1E] text-[#18181B] flex items-center justify-center font-bold text-xs transition-colors">
                <i data-lucide="chevron-right" class="w-4 h-4 text-gray-600"></i>
            </a>
        </div>
    </section>

    <!-- 4. LATEST EMULATORS & BIOS SECTION (RomsRetro Style) -->
    <section class="space-y-4">
        <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-[#CE2D2D] rounded-sm shrink-0"></span>
                <h2 class="text-base sm:text-lg font-black text-[#18181B] font-sans tracking-tight uppercase">
                    Últimos Emuladores & BIOS
                </h2>
            </div>
            <a href="{{ route('consoles.index') }}" class="px-3.5 py-1 rounded-full border border-[#1E1E1E] bg-white hover:bg-[#FAF7F2] text-xs font-mono font-bold text-[#18181B] transition-colors flex items-center gap-1 shadow-sm">
                <span>Más Emuladores</span>
                <i data-lucide="arrow-right" class="w-3 h-3 text-[#CE2D2D]"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            @php
                $emulatorsList = [
                    ['name' => 'PCSX2 BIOS & Core', 'system' => 'PlayStation 2', 'icon' => 'disc', 'size' => '14.2 MB', 'tag' => 'PS2 CORE'],
                    ['name' => 'Dolphin Emulator', 'system' => 'GameCube & Wii', 'icon' => 'box', 'size' => '22.5 MB', 'tag' => 'GC/WII'],
                    ['name' => 'PPSSPP Gold v1.17', 'system' => 'PlayStation Portable', 'icon' => 'smartphone', 'size' => '32.1 MB', 'tag' => 'PSP EMU'],
                    ['name' => 'Citra & DS Bios Pack', 'system' => 'Nintendo 3DS / DS', 'icon' => 'layers', 'size' => '18.4 MB', 'tag' => 'NDS/3DS'],
                    ['name' => 'DuckStation PS1 Pack', 'system' => 'PlayStation 1', 'icon' => 'disc-2', 'size' => '9.8 MB', 'tag' => 'PS1 EMU'],
                    ['name' => 'RPCS3 PS3 Firmware', 'system' => 'PlayStation 3', 'icon' => 'hard-drive', 'size' => '195 MB', 'tag' => 'PS3 FIRMWARE'],
                    ['name' => 'mGBA Multi-core', 'system' => 'Game Boy Advance', 'icon' => 'cpu', 'size' => '15.6 MB', 'tag' => 'GBA EMU'],
                    ['name' => 'RetroArch Ultimate Pack', 'system' => 'Multi-Sistema All-In-One', 'icon' => 'gamepad', 'size' => '85 MB', 'tag' => 'MULTI-CORE'],
                ];
            @endphp

            @foreach($emulatorsList as $emu)
                <a href="{{ route('consoles.index') }}" 
                   class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-3.5 flex items-center gap-3 hover:-translate-y-0.5 hover:shadow-md transition-all group block">
                    <div class="w-10 h-10 rounded-xl bg-[#FAF7F2] border border-[#E5E0D8] group-hover:bg-[#FDF2F2] group-hover:border-[#FCA5A5] flex items-center justify-center shrink-0 transition-colors">
                        <i data-lucide="{{ $emu['icon'] }}" class="w-5 h-5 text-[#18181B] group-hover:text-[#CE2D2D] transition-colors"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1">
                            <h4 class="text-xs font-black text-[#18181B] group-hover:text-[#CE2D2D] truncate font-sans">
                                {{ $emu['name'] }}
                            </h4>
                        </div>
                        <p class="text-[10px] font-mono text-gray-500 mt-0.5 flex items-center gap-1.5">
                            <span class="truncate">{{ $emu['system'] }}</span>
                            <span>•</span>
                            <span class="font-bold text-[#CE2D2D] shrink-0">{{ $emu['size'] }}</span>
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <!-- 5. ROMS COLLECTIONS & FRANCHISES (RomsRetro Style) -->
    <section class="space-y-4">
        <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-[#CE2D2D] rounded-sm shrink-0"></span>
                <h2 class="text-base sm:text-lg font-black text-[#18181B] font-sans tracking-tight uppercase">
                    Colecciones de ROMs
                </h2>
            </div>
            <a href="{{ route('search') }}" class="px-3.5 py-1 rounded-full border border-[#1E1E1E] bg-white hover:bg-[#FAF7F2] text-xs font-mono font-bold text-[#18181B] transition-colors flex items-center gap-1 shadow-sm">
                <span>Todas las Colecciones</span>
                <i data-lucide="arrow-right" class="w-3 h-3 text-[#CE2D2D]"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
            @php
                $franchises = [
                    ['name' => 'Pokémon Saga', 'query' => 'pokemon', 'icon' => 'sparkles'],
                    ['name' => 'The Legend of Zelda', 'query' => 'zelda', 'icon' => 'shield'],
                    ['name' => 'Super Mario', 'query' => 'mario', 'icon' => 'flame'],
                    ['name' => 'Grand Theft Auto', 'query' => 'grand theft auto', 'icon' => 'crosshair'],
                    ['name' => 'Sonic The Hedgehog', 'query' => 'sonic', 'icon' => 'zap'],
                    ['name' => 'God of War', 'query' => 'god of war', 'icon' => 'swords'],
                    ['name' => 'Tekken', 'query' => 'tekken', 'icon' => 'trophy'],
                    ['name' => 'Spider-Man', 'query' => 'spider-man', 'icon' => 'box'],
                    ['name' => 'FIFA & PES Soccer', 'query' => 'fifa', 'icon' => 'award'],
                    ['name' => 'Resident Evil', 'query' => 'resident evil', 'icon' => 'skull'],
                    ['name' => 'Dragon Ball Z', 'query' => 'dragon ball', 'icon' => 'sun'],
                    ['name' => 'Crash Bandicoot', 'query' => 'crash', 'icon' => 'smile'],
                ];
            @endphp

            @foreach($franchises as $fr)
                <a href="{{ route('search', ['q' => $fr['query']]) }}" 
                   class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-4 text-center hover:-translate-y-1 hover:shadow-md transition-all group block">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-[#FAF7F2] border border-[#E5E0D8] group-hover:bg-[#FDF2F2] group-hover:border-[#FCA5A5] flex items-center justify-center transition-colors">
                        <i data-lucide="{{ $fr['icon'] }}" class="w-6 h-6 text-[#18181B] group-hover:text-[#CE2D2D] transition-colors"></i>
                    </div>
                    <p class="text-xs font-black text-[#18181B] group-hover:text-[#CE2D2D] transition-colors truncate mt-2.5 font-sans">
                        {{ $fr['name'] }}
                    </p>
                    <p class="text-[10px] font-mono text-gray-500 uppercase mt-0.5">
                        Colección Completa
                    </p>
                </a>
            @endforeach
        </div>
    </section>

</main>
@endsection
