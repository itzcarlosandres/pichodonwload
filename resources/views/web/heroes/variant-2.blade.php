<!-- HERO DESIGN 2: Split Hardware Showcase (Asymmetric Modern Studio) -->
<section class="relative max-w-6xl mx-auto pt-3 sm:pt-6 pb-4 sm:pb-6 px-2 sm:px-4">

    <!-- Ambient Grid & Glow Texture Background -->
    <div class="absolute inset-0 -z-10 opacity-30 dark:opacity-20 pointer-events-none" 
         style="background-image: radial-gradient(rgba(206, 45, 45, 0.18) 1px, transparent 1px); background-size: 24px 24px;"></div>
    <div class="absolute -top-12 -left-12 w-72 h-72 bg-red-500/10 dark:bg-red-500/15 rounded-full blur-3xl pointer-events-none -z-10"></div>
    <div class="absolute -bottom-12 -right-12 w-80 h-80 bg-blue-500/10 dark:bg-blue-500/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">
        
        <!-- Left Column: Typography & Precision Search (7 Cols) -->
        <div class="lg:col-span-7 space-y-5 sm:space-y-6 text-left">
            
            <!-- Category Status Badge -->
            <div class="inline-flex items-center gap-2 px-3 sm:px-3.5 py-1 sm:py-1.5 rounded-full bg-white dark:bg-[#1E1E24] border border-[#E5E0D8] dark:border-[#2E2E38] shadow-2xs text-[11px] sm:text-xs font-mono font-bold text-[#18181B] dark:text-[#F4F4F5]">
                <span class="relative flex h-2 w-2 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-[#CE2D2D]"></span>
                </span>
                <span class="truncate">{{ \App\Models\Setting::get('home_hero_badge', 'ROM VAULT • 20 RETRO SYSTEMS • 100% CLEAN DUMPS') }}</span>
            </div>

            <!-- Headline with Ancient Clean Gradient Line -->
            <div class="space-y-3">
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-[#18181B] dark:text-white tracking-tight leading-[1.12]">
                    {{ \App\Models\Setting::get('home_hero_title_prefix', 'Revive los mejores') }} <br class="hidden sm:inline">
                    <span class="text-[#CE2D2D] relative inline-block">
                        {{ \App\Models\Setting::get('home_hero_title_highlight', 'clásicos de los videojuegos.') }}
                        
                        <!-- Línea original clásica con degradado en las puntas -->
                        <span class="block w-full h-[3px] sm:h-[4px] mt-1 sm:mt-1.5 rounded-full bg-gradient-to-r from-transparent via-[#CE2D2D] to-transparent pointer-events-none opacity-90"></span>
                    </span>
                </h1>
                <p class="text-xs sm:text-sm md:text-base text-gray-600 dark:text-gray-300 leading-relaxed font-sans max-w-xl">
                    {{ \App\Models\Setting::get('home_hero_description', 'Tu biblioteca digital verificada: ROMs 1:1 limpias, BIOS oficiales y emuladores listos para jugar en PC, Steam Deck, Android o consolas portátiles.') }}
                </p>
            </div>

            <!-- Search Form with Live Autocomplete and Ctrl+K Shortcut -->
            <div class="relative w-full max-w-xl text-left"
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
                                 this.$refs.heroSearchInput2?.focus();
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
                
                <form action="{{ route('search') }}" method="GET" class="p-1 sm:p-1.5 rounded-2xl bg-white dark:bg-[#18181B] border-2 border-[#1E1E1E] dark:border-[#2E2E38] shadow-[0_10px_28px_rgba(0,0,0,0.06)] dark:shadow-[0_10px_28px_rgba(0,0,0,0.4)] flex items-center gap-1.5 sm:gap-2 transition-all relative z-30 focus-within:ring-2 focus-within:ring-[#CE2D2D]/30 focus-within:border-[#CE2D2D]">
                    <div class="relative flex-1 min-w-0 flex items-center pl-2.5 sm:pl-3">
                        <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 shrink-0"></i>
                        <input 
                            type="text" 
                            name="q" 
                            x-ref="heroSearchInput2"
                            x-model="query"
                            @input="onInput()"
                            @focus="onFocus()"
                            autocomplete="off"
                            placeholder="{{ \App\Models\Setting::get('home_hero_search_placeholder', 'Buscar juego, consola o BIOS...') }}" 
                            class="w-full bg-transparent border-0 px-2 sm:px-3 py-2 text-xs sm:text-sm text-[#18181B] dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none font-sans font-medium"
                        >
                        
                        <!-- Spinner & Clear button & Keyboard Chip -->
                        <div class="flex items-center pr-1 sm:pr-2 gap-1 sm:gap-1.5 shrink-0">
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

                    <button type="submit" class="px-3.5 sm:px-6 py-2 sm:py-2.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-black text-xs uppercase tracking-wider font-sans flex items-center justify-center gap-1.5 transition-all shadow-md shadow-red-500/25 shrink-0 cursor-pointer active:scale-95">
                        <span class="hidden sm:inline">{{ \App\Models\Setting::get('home_hero_search_button', 'Buscar') }}</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </button>
                </form>

                <!-- Dropdown Partial -->
                @include('web.heroes.live-search-dropdown')
            </div>

            <!-- Quick Platform Exploration Chips -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5 max-w-xl text-xs font-mono">
                <span class="text-gray-400 dark:text-gray-500 text-[10px] sm:text-[11px] font-sans font-semibold shrink-0 mr-1">Rápido:</span>
                @php
                    $quickChips = [
                        ['name' => 'Switch', 'slug' => 'nintendo-switch', 'color' => '#E60012'],
                        ['name' => 'PS2', 'slug' => 'playstation-2', 'color' => '#003791'],
                        ['name' => 'PS4', 'slug' => 'playstation-4', 'color' => '#003791'],
                        ['name' => 'PSP', 'slug' => 'playstation-portable', 'color' => '#003791'],
                        ['name' => 'GBA', 'slug' => 'game-boy-advance', 'color' => '#5C2D91'],
                        ['name' => '3DS', 'slug' => 'nintendo-3ds', 'color' => '#CE2D2D'],
                        ['name' => 'GameCube', 'slug' => 'gamecube', 'color' => '#6B5B95'],
                    ];
                @endphp
                @foreach($quickChips as $chip)
                    <a href="{{ route('consoles.show', $chip['slug']) }}" 
                       class="px-2.5 py-1 rounded-lg bg-white dark:bg-[#1E1E24] border border-[#E5E0D8] dark:border-[#2E2E38] hover:border-[#CE2D2D] dark:hover:border-[#CE2D2D] text-gray-700 dark:text-gray-300 hover:text-[#CE2D2D] dark:hover:text-[#CE2D2D] transition-colors shadow-2xs text-[10px] sm:text-[11px] font-semibold flex items-center gap-1 shrink-0 whitespace-nowrap">
                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $chip['color'] }}"></span>
                        <span>{{ $chip['name'] }}</span>
                    </a>
                @endforeach
            </div>



        </div>

        <!-- Right Column: Interactive Hardware Showcase Station (5 Cols) -->
        <div class="lg:col-span-5">
            <div class="p-5 sm:p-6 rounded-3xl bg-[#FAF7F2] dark:bg-[#141822] border-2 border-[#1E1E1E] dark:border-[#232B3E] shadow-[0_16px_40px_rgba(0,0,0,0.08)] dark:shadow-[0_16px_40px_rgba(0,0,0,0.4)] space-y-4">
                
                <!-- Station Header -->
                <div class="flex items-center justify-between pb-3 border-b border-[#E5E0D8] dark:border-[#232B3E]">
                    <div class="flex items-center gap-2 text-xs font-mono font-bold text-[#18181B] dark:text-white">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>SISTEMAS DESTACADOS</span>
                    </div>
                    <a href="{{ route('consoles.index') }}" class="text-[11px] font-mono text-[#CE2D2D] hover:underline flex items-center gap-1 font-bold">
                        <span>Ver todas ({{ $totalConsoles ?? 20 }})</span>
                        <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                    </a>
                </div>

                <!-- 2x2 Interactive Console Cards -->
                <div class="grid grid-cols-2 gap-3">
                    @php
                        $showcaseSlugs = [
                            ['slug' => 'nintendo-switch', 'fallback_name' => 'Nintendo Switch', 'gen' => 'Gen 8 • Híbrida', 'color' => '#E60012', 'icon' => 'gamepad-2'],
                            ['slug' => 'playstation-2', 'fallback_name' => 'PlayStation 2', 'gen' => 'Gen 6 • Sony', 'color' => '#003791', 'icon' => 'disc'],
                            ['slug' => 'playstation-4', 'fallback_name' => 'PlayStation 4', 'gen' => 'Gen 8 • Sony', 'color' => '#0055D4', 'icon' => 'tv'],
                            ['slug' => 'game-boy-advance', 'fallback_name' => 'Game Boy Advance', 'gen' => 'Portátil 32-Bit', 'color' => '#5C2D91', 'icon' => 'cpu'],
                        ];
                    @endphp

                    @foreach($showcaseSlugs as $cItem)
                        @php
                            $target = strtolower(trim($cItem['slug']));
                            $matchingConsoles = $consoles->filter(function($c) use ($target) {
                                $s = strtolower(trim($c->slug));
                                $name = strtolower(trim($c->name));
                                
                                if ($s === $target) return true;
                                
                                // PlayStation 4 aliases & names
                                if ($target === 'playstation-4' && (
                                    in_array($s, ['ps4', 'playstation-4', 'playstation4', 'ps-4', 'sony-ps4']) ||
                                    str_contains($name, 'playstation 4') ||
                                    str_contains($name, 'ps4')
                                )) return true;
                                
                                // PlayStation 2 aliases & names
                                if ($target === 'playstation-2' && (
                                    in_array($s, ['ps2', 'playstation-2', 'playstation2', 'ps-2']) ||
                                    str_contains($name, 'playstation 2') ||
                                    str_contains($name, 'ps2')
                                )) return true;
                                
                                // Switch aliases & names
                                if ($target === 'nintendo-switch' && (
                                    in_array($s, ['switch', 'nintendo-switch']) ||
                                    str_contains($name, 'switch')
                                )) return true;
                                
                                // GBA aliases & names
                                if ($target === 'game-boy-advance' && (
                                    in_array($s, ['gba', 'game-boy-advance']) ||
                                    str_contains($name, 'advance') ||
                                    str_contains($name, 'gba')
                                )) return true;
                                
                                return false;
                            });

                            $realConsole = $matchingConsoles->first();
                            $cName = $realConsole ? $realConsole->name : $cItem['fallback_name'];
                            
                            $pubCount = (int) $matchingConsoles->sum('games_count');
                            $uploadedCount = (int) $matchingConsoles->sum('total_uploaded_count');
                            $cCount = $pubCount > 0 ? $pubCount : $uploadedCount;

                            $cUrl = $realConsole ? route('consoles.show', $realConsole->slug) : route('consoles.show', $cItem['slug']);
                        @endphp
                        <a href="{{ $cUrl }}" 
                           class="group p-3.5 rounded-2xl bg-white dark:bg-[#1E1E24] border border-[#E5E0D8] dark:border-[#2E2E38] hover:border-[#CE2D2D] dark:hover:border-[#CE2D2D] transition-all hover:-translate-y-1 shadow-2xs hover:shadow-lg flex flex-col justify-between h-28 relative overflow-hidden">
                            
                            <!-- Background Subtle Ambient Glow with brand color -->
                            <div class="absolute -right-6 -bottom-6 w-16 h-16 rounded-full opacity-10 group-hover:opacity-25 transition-opacity" style="background-color: {{ $cItem['color'] }}"></div>

                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-mono text-gray-400 dark:text-gray-500 uppercase">{{ $cItem['gen'] }}</span>
                                <div class="w-6 h-6 rounded-md bg-[#FAF7F2] dark:bg-[#27272A] flex items-center justify-center text-gray-400 group-hover:text-[#CE2D2D] transition-colors">
                                    <i data-lucide="arrow-right" class="w-3 h-3 group-hover:translate-x-0.5 transition-transform"></i>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xs sm:text-sm font-bold text-[#18181B] dark:text-white group-hover:text-[#CE2D2D] transition-colors truncate font-sans">
                                    {{ $cName }}
                                </h3>
                                <p class="text-[11px] font-mono text-gray-500 dark:text-gray-400 mt-0.5 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $cItem['color'] }}"></span>
                                    <span>{{ number_format($cCount) }} títulos</span>
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Live Vault Status Footer Banner -->
                <div class="p-3 rounded-2xl bg-white dark:bg-[#1E1E24] border border-[#E5E0D8] dark:border-[#2E2E38] flex items-center gap-2.5 text-[11px] font-mono text-gray-600 dark:text-gray-300 shadow-2xs">
                    <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                    <span class="truncate">
                        Bóveda activa: <strong class="text-[#CE2D2D] dark:text-red-400">{{ number_format($totalGames ?? 24800) }} ROMs</strong> disponibles
                    </span>
                </div>

            </div>
        </div>

    </div>

</section>
