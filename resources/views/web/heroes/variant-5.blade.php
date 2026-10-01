<!-- HERO DESIGN 5: Immersive Spotlight Showcase (Floating Game Art & Atmosphere) -->
<section class="relative max-w-5xl mx-auto pt-6 pb-6 text-center space-y-7 overflow-hidden">

    <!-- Ambient Multi-color Glow -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[550px] h-[300px] bg-gradient-to-r from-red-500/10 via-amber-500/10 to-blue-500/10 dark:from-red-500/15 dark:via-purple-500/10 dark:to-blue-500/15 blur-3xl rounded-full pointer-events-none -z-10"></div>

    <!-- Floating Background Game Teaser 1 (Left on desktop) -->
    @php
        $cardLeft = (isset($trendingGames) && $trendingGames->count() >= 1) ? $trendingGames[0] : null;
        $cardRight = (isset($trendingGames) && $trendingGames->count() >= 2) ? $trendingGames[1] : null;
    @endphp
    
    <div class="hidden xl:block absolute -left-2 top-8 w-44 p-2.5 rounded-2xl bg-white/80 dark:bg-[#1E1E24]/80 backdrop-blur-md border border-[#E5E0D8] dark:border-[#2E2E38] shadow-xl -rotate-6 pointer-events-none transition-transform select-none">
        <div class="w-full h-28 rounded-xl bg-gradient-to-br from-red-600 to-amber-600 overflow-hidden mb-2 flex items-center justify-center text-white font-black text-xs relative">
            @if($cardLeft && ($cardLeft->cover_thumb_url || $cardLeft->cover_url))
                <img src="{{ $cardLeft->cover_thumb_url ?: $cardLeft->cover_url }}" alt="{{ $cardLeft->title }}" class="w-full h-full object-cover">
            @else
                <div class="text-center p-2">
                    <i data-lucide="gamepad-2" class="w-8 h-8 mx-auto mb-1 text-white/80"></i>
                    <span class="text-[10px] uppercase font-mono tracking-wider">RETRO CLASSIC</span>
                </div>
            @endif
        </div>
        <div class="text-left">
            <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-red-100 dark:bg-red-950 text-[#CE2D2D] font-bold">
                {{ $cardLeft ? ($cardLeft->console ? $cardLeft->console->name : 'Nintendo') : 'Nintendo 64' }}
            </span>
            <p class="text-xs font-bold text-[#18181B] dark:text-white truncate mt-1">
                {{ $cardLeft ? $cardLeft->title : 'The Legend of Zelda' }}
            </p>
            <div class="flex items-center gap-1 text-[10px] text-amber-500 font-bold mt-0.5">
                <i data-lucide="star" class="w-3 h-3 fill-current"></i>
                <span>{{ $cardLeft ? number_format($cardLeft->rating_average ?: 4.9, 1) : '5.0' }}</span>
                <span class="text-gray-400 font-normal ml-1">★ Top 1</span>
            </div>
        </div>
    </div>

    <!-- Floating Background Game Teaser 2 (Right on desktop) -->
    <div class="hidden xl:block absolute -right-2 top-8 w-44 p-2.5 rounded-2xl bg-white/80 dark:bg-[#1E1E24]/80 backdrop-blur-md border border-[#E5E0D8] dark:border-[#2E2E38] shadow-xl rotate-6 pointer-events-none transition-transform select-none">
        <div class="w-full h-28 rounded-xl bg-gradient-to-br from-blue-700 to-indigo-900 overflow-hidden mb-2 flex items-center justify-center text-white font-black text-xs relative">
            @if($cardRight && ($cardRight->cover_thumb_url || $cardRight->cover_url))
                <img src="{{ $cardRight->cover_thumb_url ?: $cardRight->cover_url }}" alt="{{ $cardRight->title }}" class="w-full h-full object-cover">
            @else
                <div class="text-center p-2">
                    <i data-lucide="disc" class="w-8 h-8 mx-auto mb-1 text-white/80"></i>
                    <span class="text-[10px] uppercase font-mono tracking-wider">PS2 VAULT</span>
                </div>
            @endif
        </div>
        <div class="text-left">
            <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-950 text-blue-600 font-bold">
                {{ $cardRight ? ($cardRight->console ? $cardRight->console->name : 'PlayStation') : 'PlayStation 2' }}
            </span>
            <p class="text-xs font-bold text-[#18181B] dark:text-white truncate mt-1">
                {{ $cardRight ? $cardRight->title : 'God of War II' }}
            </p>
            <div class="flex items-center gap-1 text-[10px] text-amber-500 font-bold mt-0.5">
                <i data-lucide="star" class="w-3 h-3 fill-current"></i>
                <span>{{ $cardRight ? number_format($cardRight->rating_average ?: 4.8, 1) : '4.9' }}</span>
                <span class="text-gray-400 font-normal ml-1">★ Verificado</span>
            </div>
        </div>
    </div>

    <!-- Centered Content -->
    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white dark:bg-[#1E1E24] border border-[#DDD6CB] dark:border-[#2E2E38] shadow-sm text-xs font-sans font-bold text-[#18181B] dark:text-white">
        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
        <span>LA BÓVEDA DEFINITIVA EN ESPAÑOL</span>
    </div>

    <div class="space-y-3 max-w-2xl mx-auto">
        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-[#18181B] dark:text-white tracking-tight leading-[1.12]">
            Miles de clásicos <br>
            <span class="text-[#CE2D2D]">listos para jugar.</span>
        </h1>
        <p class="text-xs sm:text-base text-gray-600 dark:text-gray-300 font-sans leading-relaxed">
            Explora el catálogo retro más completo y cuidado con descargas directas sin acortadores, capturas y compatibilidad asegurada.
        </p>
    </div>

    <!-- Search Form with Live Autocomplete -->
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

        <form action="{{ route('search') }}" method="GET" class="p-2 rounded-2xl bg-white dark:bg-[#18181B] border-2 border-[#1E1E1E] dark:border-[#2E2E38] shadow-[0_15px_35px_rgba(0,0,0,0.08)] dark:shadow-[0_15px_35px_rgba(0,0,0,0.5)] flex flex-col sm:flex-row items-center gap-2 relative z-30 focus-within:ring-2 focus-within:ring-[#CE2D2D]/30 focus-within:border-[#CE2D2D]">
            
            <div class="relative flex-1 w-full flex items-center pl-3">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 dark:text-gray-500 shrink-0"></i>
                <input 
                    type="text" 
                    name="q" 
                    x-model="query"
                    @input="onInput()"
                    @focus="onFocus()"
                    autocomplete="off"
                    placeholder="Encuentra tu próximo clásico (ej. Pokemon, Zelda, GTA)..." 
                    class="w-full bg-transparent border-0 px-3 py-2 text-xs sm:text-sm text-[#18181B] dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none font-sans font-medium"
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

            <button type="submit" class="w-full sm:w-auto px-7 py-3 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-bold text-xs uppercase tracking-wider font-sans flex items-center justify-center gap-2 transition-all shadow-md shadow-red-500/25 shrink-0 cursor-pointer active:scale-95">
                <span>Buscar</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </button>
        </form>

        <!-- Dropdown Partial -->
        @include('web.heroes.live-search-dropdown')

    </div>

    <!-- Quick Navigation Anchor Tabs -->
    <div class="flex flex-wrap items-center justify-center gap-2 text-xs font-sans">
        <a href="#consolas" class="px-3 py-1.5 rounded-full bg-white dark:bg-[#1E1E24] border border-[#DDD6CB] dark:border-[#2E2E38] hover:border-[#CE2D2D] text-gray-700 dark:text-gray-300 font-semibold flex items-center gap-1.5 transition-colors shadow-2xs">
            <i data-lucide="gamepad-2" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
            <span>20 Consolas</span>
        </a>
        <a href="#populares" class="px-3 py-1.5 rounded-full bg-white dark:bg-[#1E1E24] border border-[#DDD6CB] dark:border-[#2E2E38] hover:border-[#CE2D2D] text-gray-700 dark:text-gray-300 font-semibold flex items-center gap-1.5 transition-colors shadow-2xs">
            <i data-lucide="flame" class="w-3.5 h-3.5 text-amber-500"></i>
            <span>Más Populares</span>
        </a>
        <a href="#recientes" class="px-3 py-1.5 rounded-full bg-white dark:bg-[#1E1E24] border border-[#DDD6CB] dark:border-[#2E2E38] hover:border-[#CE2D2D] text-gray-700 dark:text-gray-300 font-semibold flex items-center gap-1.5 transition-colors shadow-2xs">
            <i data-lucide="clock" class="w-3.5 h-3.5 text-blue-500"></i>
            <span>Novedades</span>
        </a>
        <a href="{{ route('emulators') }}" class="px-3 py-1.5 rounded-full bg-white dark:bg-[#1E1E24] border border-[#DDD6CB] dark:border-[#2E2E38] hover:border-[#CE2D2D] text-gray-700 dark:text-gray-300 font-semibold flex items-center gap-1.5 transition-colors shadow-2xs">
            <i data-lucide="cpu" class="w-3.5 h-3.5 text-purple-500"></i>
            <span>Emuladores</span>
        </a>
        <a href="{{ route('bios') }}" class="px-3 py-1.5 rounded-full bg-white dark:bg-[#1E1E24] border border-[#DDD6CB] dark:border-[#2E2E38] hover:border-[#CE2D2D] text-gray-700 dark:text-gray-300 font-semibold flex items-center gap-1.5 transition-colors shadow-2xs">
            <i data-lucide="hard-drive" class="w-3.5 h-3.5 text-emerald-500"></i>
            <span>Archivos BIOS</span>
        </a>
    </div>

</section>
