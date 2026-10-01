<!-- HERO DESIGN 1: Cyber-Minimal Vault (Refined Precision, Mobile Optimized & Clean Minimalist) -->
<section class="relative text-center space-y-4 sm:space-y-6 max-w-4xl mx-auto pt-3 sm:pt-6 pb-2 sm:pb-4 px-2 sm:px-4">
    
    <!-- Ambient subtle background glow -->
    <div class="absolute -top-16 left-1/2 -translate-x-1/2 w-72 sm:w-96 h-72 sm:h-96 bg-red-500/10 dark:bg-red-500/15 rounded-full blur-3xl pointer-events-none -z-10"></div>

    <!-- Retro Red Status Badge -->
    <div class="inline-flex items-center gap-2 px-3 sm:px-4 py-1 sm:py-1.5 rounded-full bg-white dark:bg-[#1E1E24] border border-[#E5E0D8] dark:border-[#2E2E38] shadow-sm text-[11px] sm:text-xs font-mono font-bold max-w-full">
        <span class="relative flex h-2 w-2 shrink-0">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#CE2D2D]"></span>
        </span>
        <span class="text-[#18181B] dark:text-[#F4F4F5] tracking-wide truncate">{{ \App\Models\Setting::get('home_hero_badge', 'ROM VAULT • 20 RETRO SYSTEMS • 100% CLEAN DUMPS') }}</span>
    </div>

    <!-- Main Headline with Crisp Red Highlight & Responsive Typography -->
    <div class="space-y-2.5 sm:space-y-3.5">
        <h1 class="text-2xl xs:text-3xl sm:text-5xl lg:text-6xl font-black text-[#18181B] dark:text-white tracking-tight font-sans leading-[1.14] sm:leading-[1.12]">
            {{ \App\Models\Setting::get('home_hero_title_prefix', 'Retro ROMs & Emulators for') }} <br class="hidden sm:inline">
            <span class="text-[#CE2D2D] relative inline-block">
                {{ \App\Models\Setting::get('home_hero_title_highlight', 'every classic console.') }}
                
                <!-- Modelo de línea original con degradado en las puntas y destello Flash infinito -->
                <span class="block relative w-full h-[3px] sm:h-[4px] mt-1 sm:mt-1.5 overflow-hidden rounded-full pointer-events-none"
                      style="-webkit-mask-image: linear-gradient(to right, transparent 0%, black 15%, black 85%, transparent 100%); mask-image: linear-gradient(to right, transparent 0%, black 15%, black 85%, transparent 100%);">
                    <!-- Línea roja base idéntica al modelo original (degradado en las puntas) -->
                    <span class="absolute inset-0 bg-gradient-to-r from-transparent via-[#CE2D2D] to-transparent"></span>
                    <span class="absolute inset-0 bg-gradient-to-r from-transparent via-[#CE2D2D] to-transparent blur-[2px] opacity-75"></span>
                    
                    <!-- Destello estilo Flash que recorre la línea infinitamente -->
                    <span class="hero-flash-ray absolute inset-y-0 w-1/3 bg-gradient-to-r from-transparent via-white via-red-100 to-transparent"></span>
                </span>
            </span>
        </h1>
        <p class="text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-300 max-w-xl mx-auto font-sans leading-relaxed font-normal px-2">
            {{ \App\Models\Setting::get('home_hero_description', 'Descarga videojuegos retro verificados (No-Intro / Redump), archivos BIOS y emuladores para PlayStation, Nintendo, Sega, Xbox y más de 15 sistemas clásicos.') }}
        </p>
    </div>

    <!-- Precision Search Form (Mobile Single-Row Capsule with Live Autocomplete and Shortcut Ctrl+K) -->
    <div class="relative max-w-2xl mx-auto text-left"
         x-data="{
             query: '',
             results: [],
             loading: false,
             open: false,
             timeout: null,
             init() {
                 window.addEventListener('keydown', (e) => {
                     if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                         e.preventDefault();
                         this.$refs.heroSearchInput1?.focus();
                     }
                 });
             },
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
                 }, 220);
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
        
        <form action="{{ route('search') }}" method="GET" class="p-1 sm:p-1.5 md:p-2 rounded-2xl bg-white dark:bg-[#18181B] border-2 border-[#1E1E1E] dark:border-[#2E2E38] shadow-[0_10px_28px_rgba(0,0,0,0.06)] dark:shadow-[0_10px_28px_rgba(0,0,0,0.4)] flex items-center gap-1.5 sm:gap-2 transition-all relative z-30 focus-within:ring-2 focus-within:ring-[#CE2D2D]/30 focus-within:border-[#CE2D2D]">
            
            <!-- Search Text Input -->
            <div class="relative flex-1 min-w-0 flex items-center pl-2.5 sm:pl-3">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 shrink-0"></i>
                <input 
                    type="text" 
                    name="q" 
                    x-ref="heroSearchInput1"
                    x-model="query"
                    @input="onInput()"
                    @focus="onFocus()"
                    autocomplete="off"
                    placeholder="{{ \App\Models\Setting::get('home_hero_search_placeholder', 'Buscar juego, consola o BIOS...') }}" 
                    class="w-full bg-transparent border-0 px-2 sm:px-3 py-2 text-xs sm:text-sm text-[#18181B] dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none font-sans font-medium"
                >
                
                <!-- Keyboard Shortcut Badge & Clear button & Spinner -->
                <div class="flex items-center pr-1.5 sm:pr-2 gap-1 sm:gap-1.5 shrink-0">
                    <template x-if="loading">
                        <div class="w-3.5 sm:w-4 h-3.5 sm:h-4 border-2 border-[#CE2D2D] border-t-transparent rounded-full animate-spin"></div>
                    </template>
                    <template x-if="query.length > 0 && !loading">
                        <button type="button" @click="query = ''; results = []; open = false" class="p-1 text-gray-400 hover:text-black dark:hover:text-white cursor-pointer">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </template>
                    <span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-medium text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-[#27272A] border border-gray-200 dark:border-gray-700">
                        Ctrl+K
                    </span>
                </div>
            </div>

            <!-- Red Search Action Button (Compact on mobile, full on desktop) -->
            <button type="submit" class="px-3.5 sm:px-7 py-2 sm:py-3 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-black text-xs uppercase tracking-wider font-sans flex items-center justify-center gap-1.5 sm:gap-2 transition-all shadow-md shadow-red-500/25 shrink-0 cursor-pointer active:scale-95">
                <span class="hidden sm:inline">{{ \App\Models\Setting::get('home_hero_search_button', 'Buscar') }}</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </button>
        </form>

        <!-- Dropdown Partial -->
        @include('web.heroes.live-search-dropdown')

    </div>

    <!-- Quick Console Filter Badges (Horizontal smooth swipe on mobile, centered wrap on desktop) -->
    <div class="flex items-center justify-start sm:justify-center gap-1.5 sm:gap-2 overflow-x-auto no-scrollbar py-1 px-1 -mx-2 sm:mx-0 sm:flex-wrap text-xs font-mono">
        <span class="text-gray-400 dark:text-gray-500 text-[10px] sm:text-[11px] font-sans font-semibold shrink-0 mr-0.5">Explorar:</span>
        @php
            $quickSystems = [
                ['name' => 'Nintendo Switch', 'slug' => 'nintendo-switch', 'icon' => 'gamepad-2'],
                ['name' => 'PlayStation 2', 'slug' => 'playstation-2', 'icon' => 'disc'],
                ['name' => 'Game Boy Advance', 'slug' => 'game-boy-advance', 'icon' => 'cpu'],
                ['name' => 'GameCube', 'slug' => 'gamecube', 'icon' => 'box'],
                ['name' => 'PSP', 'slug' => 'playstation-portable', 'icon' => 'layers'],
                ['name' => 'Nintendo 3DS', 'slug' => 'nintendo-3ds', 'icon' => 'sparkles'],
            ];
        @endphp
        @foreach($quickSystems as $sys)
            <a href="{{ route('consoles.show', $sys['slug']) }}" 
               class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#1E1E24] border border-[#E5E0D8] dark:border-[#2E2E38] hover:border-[#CE2D2D] dark:hover:border-[#CE2D2D] text-gray-700 dark:text-gray-300 hover:text-[#CE2D2D] dark:hover:text-[#CE2D2D] transition-colors shadow-2xs text-[10px] sm:text-[11px] font-semibold flex items-center gap-1.5 shrink-0 whitespace-nowrap">
                <i data-lucide="{{ $sys['icon'] }}" class="w-3 h-3 text-gray-400 dark:text-gray-500"></i>
                <span>{{ $sys['name'] }}</span>
            </a>
        @endforeach
    </div>

    <!-- Hero Underline Flash Animation Styles -->
    <style>
    @keyframes heroFlashSweep {
        0% {
            transform: translateX(-160%);
        }
        100% {
            transform: translateX(360%);
        }
    }
    .hero-flash-ray {
        animation: heroFlashSweep 2.2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        filter: drop-shadow(0 0 6px rgba(255, 255, 255, 0.95)) drop-shadow(0 0 10px rgba(206, 45, 45, 0.85));
        will-change: transform;
    }
    </style>

</section>
