<!-- HERO DESIGN 3: Swiss Editorial Catalog (Akihabara Archive Discipline) -->
<section class="relative max-w-4xl mx-auto pt-6 pb-6 text-center space-y-6">

    <!-- Top Monospace Coordinate Strip -->
    <div class="inline-flex items-center gap-3 px-3 py-1 bg-white dark:bg-[#1E1E24] border border-[#DDD6CB] dark:border-[#2E2E38] text-[11px] font-mono tracking-wider text-gray-600 dark:text-gray-400">
        <span class="w-1.5 h-1.5 bg-[#CE2D2D]"></span>
        <span>INDEX.SYS-20</span>
        <span class="text-gray-300 dark:text-gray-700">|</span>
        <span>NO-INTRO / REDUMP STANDARD</span>
        <span class="text-gray-300 dark:text-gray-700">|</span>
        <span class="text-[#CE2D2D] font-bold">{{ number_format($totalGames ?? 24800) }} DUMPS</span>
    </div>

    <!-- Editorial High-Impact Headline -->
    <div class="space-y-3">
        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-[#18181B] dark:text-white uppercase tracking-tight font-sans leading-[1.08]">
            Catálogo Definitivo de <br>
            <span class="bg-[#CE2D2D] text-white px-2 py-0.5 inline-block -rotate-1 mt-1">Videojuegos Clásicos</span>
        </h1>
        <p class="text-xs sm:text-sm font-mono text-gray-600 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed">
            [ACCESO DIRECTO] Preservación histórica de software interactivo sin publicidad invasiva ni programas de instalación de terceros.
        </p>
    </div>

    <!-- Multi-Segment Search Dock (Console Filter + Input + Action) -->
    <div class="relative max-w-3xl mx-auto text-left"
         x-data="{
             query: '',
             selectedConsole: '',
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

        <form action="{{ route('search') }}" method="GET" class="p-1 sm:p-1.5 bg-white dark:bg-[#18181B] border-2 border-[#DDD6CB] dark:border-[#383842] shadow-[4px_4px_0px_#DDD6CB] dark:shadow-[4px_4px_0px_#CE2D2D] flex flex-col sm:flex-row items-stretch gap-1 relative z-30">
            
            <!-- Console Selector Dropdown Segment -->
            <div class="sm:w-48 bg-[#FAF7F2] dark:bg-[#202025] border-b sm:border-b-0 sm:border-r border-[#E5E0D8] dark:border-[#2E2E35] flex items-center px-3">
                <i data-lucide="filter" class="w-3.5 h-3.5 text-gray-500 mr-2 shrink-0"></i>
                <select name="console" x-model="selectedConsole" class="w-full bg-transparent border-0 text-xs font-mono font-bold text-[#18181B] dark:text-gray-200 py-2.5 focus:outline-none cursor-pointer">
                    <option value="" class="dark:bg-[#18181B]">Todas las consolas</option>
                    @foreach($consoles as $con)
                        <option value="{{ $con->slug }}" class="dark:bg-[#18181B]">{{ $con->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search Text Input -->
            <div class="relative flex-1 flex items-center pl-3">
                <i data-lucide="terminal" class="w-3.5 h-3.5 text-[#CE2D2D] shrink-0 mr-2"></i>
                <input 
                    type="text" 
                    name="q" 
                    x-model="query"
                    @input="onInput()"
                    @focus="onFocus()"
                    autocomplete="off"
                    placeholder="Escribe título, franquicia o código..." 
                    class="w-full bg-transparent border-0 px-2 py-2 text-xs sm:text-sm font-mono text-[#18181B] dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none"
                >
                <template x-if="loading">
                    <div class="w-3.5 h-3.5 border-2 border-[#CE2D2D] border-t-transparent rounded-full animate-spin mr-2"></div>
                </template>
                <template x-if="query.length > 0 && !loading">
                    <button type="button" @click="query = ''; results = []; open = false" class="p-1 text-gray-400 hover:text-black dark:hover:text-white mr-2 cursor-pointer">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </template>
            </div>

            <!-- Swiss Monospace Action Button -->
            <button type="submit" class="px-7 py-3 bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-mono font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-colors shrink-0 cursor-pointer">
                <span>EJECUTAR BÚSQUEDA</span>
                <i data-lucide="corner-down-left" class="w-3.5 h-3.5"></i>
            </button>
        </form>

        <!-- Dropdown Partial -->
        @include('web.heroes.live-search-dropdown')

    </div>

    <!-- Monospace Data Ticks Bar -->
    <div class="pt-2 flex flex-wrap items-center justify-center gap-2 text-[11px] font-mono">
        @php
            $swissPills = [
                ['name' => 'PS2', 'slug' => 'playstation-2'],
                ['name' => 'SWITCH', 'slug' => 'nintendo-switch'],
                ['name' => 'GBA', 'slug' => 'game-boy-advance'],
                ['name' => 'PSP', 'slug' => 'playstation-portable'],
                ['name' => 'GAMECUBE', 'slug' => 'gamecube'],
                ['name' => 'NDS', 'slug' => 'nintendo-ds'],
            ];
        @endphp
        <span class="text-gray-400 dark:text-gray-500 font-bold uppercase mr-1">ACCESO RÁPIDO:</span>
        @foreach($swissPills as $sp)
            @php
                $conItem = $consoles->firstWhere('slug', $sp['slug']);
                $cnt = $conItem ? $conItem->games_count : 0;
            @endphp
            <a href="{{ route('consoles.show', $sp['slug']) }}" 
               class="px-2.5 py-1 bg-white dark:bg-[#1E1E24] border border-[#DDD6CB] dark:border-[#2E2E38] hover:border-[#1E1E1E] dark:hover:border-white text-gray-700 dark:text-gray-300 font-bold transition-colors">
                [{{ $sp['name'] }}: {{ $cnt }}]
            </a>
        @endforeach
    </div>

</section>
