@extends('layouts.web')

@section('title', \App\Models\Setting::get('seo_meta_title', \App\Models\Setting::get('site_name', 'ROMHUB') . ' — Retro ROMs & Emulators for Every Classic Console'))

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-8 space-y-14">

    <!-- 1. HERO SECTION (Split Hardware Showcase) -->
    @include('web.heroes.variant-2')


    <!-- 2. CONSOLES SECTION (Horizontal Smooth Slider with Autoplay, Mouse Drag & Mobile Optimization) -->
    <section class="space-y-3 sm:space-y-4" x-data="{
        isDown: false,
        startX: 0,
        scrollLeftPos: 0,
        isDragging: false,
        timer: null,
        isPaused: false,

        initSlider() {
            this.startAuto();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },
        startAuto() {
            this.stopAuto();
            this.timer = setInterval(() => {
                if (this.isDown || this.isPaused) return;
                const el = this.$refs.consolesSlider;
                if (!el) return;
                const maxScroll = el.scrollWidth - el.clientWidth;
                if (el.scrollLeft >= maxScroll - 15) {
                    el.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    el.scrollBy({ left: 220, behavior: 'smooth' });
                }
            }, 3200);
        },
        stopAuto() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },
        scrollLeft() {
            this.isPaused = true;
            this.$refs.consolesSlider.scrollBy({ left: -260, behavior: 'smooth' });
            setTimeout(() => { this.isPaused = false; }, 3500);
        },
        scrollRight() {
            this.isPaused = true;
            this.$refs.consolesSlider.scrollBy({ left: 260, behavior: 'smooth' });
            setTimeout(() => { this.isPaused = false; }, 3500);
        },
        onMouseDown(e) {
            this.isDown = true;
            this.isDragging = false;
            this.isPaused = true;
            const el = this.$refs.consolesSlider;
            this.startX = e.pageX - el.offsetLeft;
            this.scrollLeftPos = el.scrollLeft;
        },
        onMouseMove(e) {
            if (!this.isDown) return;
            e.preventDefault();
            const el = this.$refs.consolesSlider;
            const x = e.pageX - el.offsetLeft;
            const walk = (x - this.startX) * 1.4;
            if (Math.abs(walk) > 6) {
                this.isDragging = true;
            }
            el.scrollLeft = this.scrollLeftPos - walk;
        },
        onMouseUp() {
            this.isDown = false;
            setTimeout(() => { 
                this.isDragging = false; 
                this.isPaused = false;
            }, 120);
        },
        onLinkClick(e) {
            if (this.isDragging) {
                e.preventDefault();
                e.stopPropagation();
            }
        }
    }" 
    x-init="initSlider()"
    @mouseleave="isDown = false; isPaused = false;">
        <div class="flex items-center justify-between border-b border-[#E5E0D8] dark:border-[#27272A] pb-3">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 bg-[#CE2D2D] rounded-sm shrink-0"></span>
                <h2 class="text-base sm:text-lg font-black text-[#18181B] dark:text-white font-sans tracking-tight uppercase">
                    Consoles
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('consoles.index') }}" class="px-3 py-1 rounded-full border border-[#1E1E1E] dark:border-[#27272A] bg-white dark:bg-[#18181B] hover:bg-[#FAF7F2] dark:hover:bg-[#27272A] text-xs font-mono font-bold text-[#18181B] dark:text-white transition-colors flex items-center gap-1">
                    <span>Ver las {{ $totalConsoles }} consolas</span>
                    <i data-lucide="arrow-right" class="w-3 h-3 text-[#CE2D2D]"></i>
                </a>

                <!-- Flechas de navegación suave -->
                <div class="flex items-center gap-1">
                    <button type="button" 
                            @click="scrollLeft()" 
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl border border-[#1E1E1E] dark:border-[#27272A] bg-white dark:bg-[#18181B] hover:bg-[#CE2D2D] hover:text-white hover:border-[#CE2D2D] dark:hover:bg-[#CE2D2D] dark:hover:border-[#CE2D2D] text-[#18181B] dark:text-white flex items-center justify-center transition-all cursor-pointer shadow-xs active:scale-90" 
                            title="Desplazar a la izquierda"
                            aria-label="Anterior">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                    <button type="button" 
                            @click="scrollRight()" 
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl border border-[#1E1E1E] dark:border-[#27272A] bg-white dark:bg-[#18181B] hover:bg-[#CE2D2D] hover:text-white hover:border-[#CE2D2D] dark:hover:bg-[#CE2D2D] dark:hover:border-[#CE2D2D] text-[#18181B] dark:text-white flex items-center justify-center transition-all cursor-pointer shadow-xs active:scale-90" 
                            title="Desplazar a la derecha"
                            aria-label="Siguiente">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- 20 Consoles Single-Line Slider (Automático, drag con mouse, swipe móvil) -->
        <div x-ref="consolesSlider" 
             @mousedown="onMouseDown($event)"
             @mousemove="onMouseMove($event)"
             @mouseup="onMouseUp()"
             @mouseenter="isPaused = true"
             @mouseleave="onMouseUp(); isPaused = false"
             @touchstart.passive="isPaused = true"
             @touchend.passive="setTimeout(() => isPaused = false, 2000)"
             class="flex items-stretch gap-2.5 sm:gap-3.5 overflow-x-auto scroll-smooth py-1.5 px-0.5 no-scrollbar select-none cursor-grab active:cursor-grabbing touch-pan-x"
             style="scrollbar-width: none; -ms-overflow-style: none; -webkit-overflow-scrolling: touch;">
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
                   @click="onLinkClick($event)"
                   draggable="false"
                   class="flex-none w-[105px] sm:w-[130px] bg-white dark:bg-[#18181B] border-2 border-[#1E1E1E] dark:border-[#27272A] rounded-2xl p-2.5 sm:p-3.5 text-center hover:-translate-y-1 active:scale-[0.98] transition-all duration-200 shadow-sm hover:shadow-md group block select-none">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 mx-auto rounded-xl bg-[#FAF7F2] dark:bg-[#202024] border border-[#E5E0D8] dark:border-[#2E2E33] group-hover:bg-[#FDF2F2] dark:group-hover:bg-red-950/40 group-hover:border-[#FCA5A5] dark:group-hover:border-red-800 flex items-center justify-center transition-colors pointer-events-none">
                        <i data-lucide="{{ $cIcon }}" class="w-4.5 h-4.5 sm:w-5 sm:h-5 text-[#18181B] dark:text-[#E4E4E7] group-hover:text-[#CE2D2D] transition-colors"></i>
                    </div>
                    <p class="text-[11px] sm:text-[13px] font-black text-[#18181B] dark:text-white group-hover:text-[#CE2D2D] transition-colors truncate mt-2 font-sans pointer-events-none">
                        {{ $con->short_name ?: $con->name }}
                    </p>
                    <p class="text-[9px] sm:text-[10px] font-mono font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mt-0.5 pointer-events-none">
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
                <div class="group relative flex flex-col justify-between space-y-2 cursor-pointer">
                    
                    <!-- Seamless Floating Cover with Smooth Lift & Red Glow on Hover -->
                    <div class="relative aspect-[3/4.2] rounded-2xl overflow-hidden border border-[#E5E0D8] dark:border-white/[0.08] hover:border-[#CE2D2D]/60 dark:hover:border-[#CE2D2D]/60 shadow-md hover:shadow-2xl hover:shadow-red-600/25 transition-all duration-400 group-hover:-translate-y-1.5 bg-[#FAF7F2] dark:bg-[#090C12]">
                        <a href="{{ route('game.show', $game->slug) }}" class="block w-full h-full">
                            <img src="{{ $game->cover_thumb_url ?: $game->cover_url }}" 
                                 alt="{{ $game->title }}" 
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='{{ asset('images/placeholder-cover.svg') }}';"
                                 class="w-full h-full object-cover object-center group-hover:scale-106 transition-transform duration-500 ease-out">
                        </a>

                        <!-- Subtle Bottom Vignette on the Artwork -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent pointer-events-none"></div>

                        <!-- Floating Console Tag on Top-Left -->
                        <div class="absolute top-2.5 left-2.5 z-10 pointer-events-none">
                            <span class="px-2 py-0.5 rounded-lg bg-black/65 backdrop-blur-md border border-white/15 text-[9px] font-mono font-bold text-white shadow-sm">
                                {{ $game->console->short_name ?: $game->console->name }}
                            </span>
                        </div>

                        <!-- Floating Special Badge on Top-Right -->
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

                        <!-- Size Pill on Bottom-Left (Fades smoothly on hover) -->
                        <div class="absolute bottom-2.5 left-2.5 z-10 px-2 py-0.5 rounded-md bg-black/75 backdrop-blur-md text-[10px] font-mono text-gray-200 border border-white/10 group-hover:opacity-0 transition-opacity duration-200 pointer-events-none">
                            {{ $game->formatted_size }}
                        </div>

                        <!-- Rating Pill on Bottom-Right (Fades smoothly on hover) -->
                        <div class="absolute bottom-2.5 right-2.5 z-10 px-2 py-0.5 rounded-md bg-black/75 backdrop-blur-md text-[10px] font-mono text-amber-400 font-bold border border-white/10 flex items-center gap-1 group-hover:opacity-0 transition-opacity duration-200 pointer-events-none">
                            <span>★</span>
                            <span>{{ number_format($game->rating_average, 1) }}</span>
                        </div>

                        <!-- Luminous Light Ray Streak Effect on Hover ("Raya de luz") -->
                        <div class="card-shine-ray"></div>

                        <!-- Red Brand Color Hover Download Button (#CE2D2D) -->
                        <div class="absolute inset-x-2.5 bottom-2.5 z-20 opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-300 ease-out pointer-events-auto">
                            <a href="{{ route('game.show', $game->slug) }}" class="w-full py-2 px-3 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-instrument text-xs font-bold flex items-center justify-center gap-1.5 shadow-xl shadow-red-600/40 backdrop-blur-md active:scale-95 transition-all text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                <span>Descargar ROM</span>
                            </a>
                        </div>
                    </div>

                    <!-- Title in Instrument Sans -->
                    <div class="px-0.5 pt-0.5 space-y-0.5">
                        <h3 class="font-instrument text-[13px] sm:text-[14px] font-bold text-[#18181B] dark:text-white group-hover:text-[#CE2D2D] dark:group-hover:text-[#CE2D2D] transition-colors line-clamp-1 tracking-tight leading-snug">
                            <a href="{{ route('game.show', $game->slug) }}" class="card-title-line inline">{{ $game->title }}</a>
                        </h3>
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
