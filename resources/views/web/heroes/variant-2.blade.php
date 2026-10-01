<!-- HERO DESIGN 2: Split Hardware Showcase (Asymmetric Modern Studio) -->
<section class="relative max-w-6xl mx-auto pt-4 pb-6">

    <!-- Ambient Grid Texture background -->
    <div class="absolute inset-0 -z-10 opacity-30 dark:opacity-20 pointer-events-none" 
         style="background-image: radial-gradient(rgba(206, 45, 45, 0.15) 1px, transparent 1px); background-size: 24px 24px;"></div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
        
        <!-- Left Column: Typography & Command Search (7 Cols) -->
        <div class="lg:col-span-7 space-y-6 text-left">
            
            <!-- Category Tag -->
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-[#CE2D2D]/10 text-[#CE2D2D] dark:bg-[#CE2D2D]/20 border border-[#CE2D2D]/30 text-xs font-mono font-bold tracking-wider">
                <i data-lucide="disc" class="w-3.5 h-3.5 animate-spin" style="animation-duration: 4s;"></i>
                <span>RETRO PRESERVATION VAULT // NO-INTRO</span>
            </div>

            <!-- Headline -->
            <div class="space-y-3">
                <h1 class="text-3xl sm:text-5xl lg:text-5xl font-black text-[#18181B] dark:text-white tracking-tight leading-[1.12]">
                    Revive los mejores <br>
                    <span class="text-[#CE2D2D]">clásicos de los videojuegos.</span>
                </h1>
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 leading-relaxed font-sans max-w-xl">
                    Tu biblioteca digital verificada: ROMs 1:1 limpias, BIOS oficiales y emuladores listos para jugar en PC, Steam Deck, Android o consolas portátiles.
                </p>
            </div>

            <!-- Search Form with Live Autocomplete -->
            <div class="relative w-full max-w-xl"
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
                
                <form action="{{ route('search') }}" method="GET" class="p-1.5 rounded-2xl bg-white dark:bg-[#18181B] border-2 border-[#1E1E1E] dark:border-[#2E2E38] shadow-[0_10px_25px_rgba(0,0,0,0.06)] dark:shadow-[0_10px_25px_rgba(0,0,0,0.4)] flex items-center gap-2 transition-all relative z-30 focus-within:ring-2 focus-within:ring-[#CE2D2D]/30 focus-within:border-[#CE2D2D]">
                    <div class="relative flex-1 flex items-center pl-3">
                        <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 shrink-0"></i>
                        <input 
                            type="text" 
                            name="q" 
                            x-model="query"
                            @input="onInput()"
                            @focus="onFocus()"
                            autocomplete="off"
                            placeholder="Buscar juego por nombre (ej. Zelda, Mario, God of War)..." 
                            class="w-full bg-transparent border-0 px-3 py-2.5 text-xs sm:text-sm text-[#18181B] dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none font-sans font-medium"
                        >
                        <template x-if="loading">
                            <div class="w-4 h-4 border-2 border-[#CE2D2D] border-t-transparent rounded-full animate-spin mr-2"></div>
                        </template>
                        <template x-if="query.length > 0 && !loading">
                            <button type="button" @click="query = ''; results = []; open = false" class="p-1 text-gray-400 hover:text-black dark:hover:text-white mr-2 cursor-pointer">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </template>
                    </div>

                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-bold text-xs uppercase tracking-wider font-sans flex items-center gap-1.5 transition-all shadow-md shadow-red-500/25 shrink-0 cursor-pointer active:scale-95">
                        <span>Buscar</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </button>
                </form>

                <!-- Dropdown Partial -->
                @include('web.heroes.live-search-dropdown')
            </div>

            <!-- Trust Metrics Strip -->
            <div class="grid grid-cols-3 gap-3 pt-2 max-w-xl">
                <div class="p-3 rounded-xl bg-white dark:bg-[#1E1E24] border border-[#E5E0D8] dark:border-[#2E2E38] shadow-2xs">
                    <div class="text-xs font-mono font-bold text-[#CE2D2D] flex items-center gap-1">
                        <i data-lucide="gamepad-2" class="w-3.5 h-3.5"></i>
                        <span>{{ $totalConsoles ?? 20 }}+ Consolas</span>
                    </div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Sistemas soportados</div>
                </div>

                <div class="p-3 rounded-xl bg-white dark:bg-[#1E1E24] border border-[#E5E0D8] dark:border-[#2E2E38] shadow-2xs">
                    <div class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                        <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                        <span>100% Limpio</span>
                    </div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Dumps verificados</div>
                </div>

                <div class="p-3 rounded-xl bg-white dark:bg-[#1E1E24] border border-[#E5E0D8] dark:border-[#2E2E38] shadow-2xs">
                    <div class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400 flex items-center gap-1">
                        <i data-lucide="fast-forward" class="w-3.5 h-3.5"></i>
                        <span>Direct CDN</span>
                    </div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">Sin tiempos de espera</div>
                </div>
            </div>

        </div>

        <!-- Right Column: Interactive Hardware Cards (5 Cols) -->
        <div class="lg:col-span-5">
            <div class="p-5 rounded-3xl bg-[#FAF7F2] dark:bg-[#151518] border-2 border-[#1E1E1E] dark:border-[#2E2E38] shadow-[0_16px_40px_rgba(0,0,0,0.08)] space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-[#E5E0D8] dark:border-[#27272A]">
                    <div class="flex items-center gap-2 text-xs font-mono font-bold text-[#18181B] dark:text-white">
                        <span class="w-2 h-2 rounded-full bg-[#CE2D2D] animate-ping"></span>
                        <span>SISTEMAS DESTACADOS</span>
                    </div>
                    <a href="{{ route('consoles.index') }}" class="text-[11px] font-mono text-[#CE2D2D] hover:underline flex items-center gap-1">
                        <span>Ver todos</span>
                        <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                    </a>
                </div>

                <!-- 2x2 Interactive Mini Console Cards -->
                <div class="grid grid-cols-2 gap-3">
                    @php
                        $showcaseSlugs = [
                            ['slug' => 'nintendo-switch', 'fallback_name' => 'Nintendo Switch', 'gen' => 'Gen 8 / Híbrida', 'color' => '#E60012'],
                            ['slug' => 'playstation-2', 'fallback_name' => 'PlayStation 2', 'gen' => 'Gen 6 / Sony', 'color' => '#003791'],
                            ['slug' => 'gamecube', 'fallback_name' => 'GameCube', 'gen' => 'Gen 6 / Nintendo', 'color' => '#6B5B95'],
                            ['slug' => 'game-boy-advance', 'fallback_name' => 'GBA', 'gen' => 'Portátil 32-Bit', 'color' => '#5C2D91'],
                        ];
                    @endphp

                    @foreach($showcaseSlugs as $cItem)
                        @php
                            $realConsole = $consoles->firstWhere('slug', $cItem['slug']);
                            $cName = $realConsole ? $realConsole->name : $cItem['fallback_name'];
                            $cCount = $realConsole ? $realConsole->games_count : 500;
                            $cUrl = $realConsole ? route('consoles.show', $realConsole->slug) : route('search', ['console' => $cItem['slug']]);
                            $cIcon = $realConsole && $realConsole->icon_url ? $realConsole->icon_url : null;
                        @endphp
                        <a href="{{ $cUrl }}" class="group p-3.5 rounded-2xl bg-white dark:bg-[#1E1E24] border border-[#E5E0D8] dark:border-[#2E2E38] hover:border-[#CE2D2D] dark:hover:border-[#CE2D2D] transition-all hover:-translate-y-1 shadow-2xs hover:shadow-md flex flex-col justify-between h-28 relative overflow-hidden">
                            <!-- Background accent glow -->
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
                                <p class="text-[11px] font-mono text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ number_format($cCount) }} títulos
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Footer tip -->
                <div class="p-2.5 rounded-xl bg-white dark:bg-[#1E1E24] border border-[#E5E0D8] dark:border-[#2E2E38] flex items-center gap-2.5 text-[11px] text-gray-600 dark:text-gray-400">
                    <i data-lucide="info" class="w-3.5 h-3.5 text-[#CE2D2D] shrink-0"></i>
                    <span>Todos los juegos incluyen carátulas 3D, sinopsis y enlaces verificados.</span>
                </div>

            </div>
        </div>

    </div>

</section>
