<!-- HERO DESIGN 4: Neo-Terminal Command Deck (Retro Tech & Clean HUD) -->
<section class="relative max-w-4xl mx-auto pt-4 pb-6">

    <!-- Tech Container HUD Frame -->
    <div class="relative p-6 sm:p-8 rounded-3xl bg-black/5 dark:bg-black/40 border border-[#1E1E1E]/20 dark:border-white/10 backdrop-blur-md overflow-hidden text-center space-y-6">
        
        <!-- Corner Tech HUD Accents -->
        <div class="absolute top-2 left-3 text-[10px] font-mono text-[#CE2D2D]/60 select-none">┌ [SYS-01]</div>
        <div class="absolute top-2 right-3 text-[10px] font-mono text-[#CE2D2D]/60 select-none">[SEC-VLT] ┐</div>
        <div class="absolute bottom-2 left-3 text-[10px] font-mono text-[#CE2D2D]/60 select-none">└ [READY]</div>
        <div class="absolute bottom-2 right-3 text-[10px] font-mono text-[#CE2D2D]/60 select-none">[ONLINE] ┘</div>

        <!-- Terminal Status Bar -->
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded bg-black text-[#4ADE80] font-mono text-xs border border-[#4ADE80]/30 shadow-[0_0_12px_rgba(74,222,128,0.15)]">
            <span class="w-2 h-2 rounded-full bg-[#4ADE80] animate-pulse"></span>
            <span>CORE::ACTIVE</span>
            <span class="text-white/40">|</span>
            <span class="text-gray-300">{{ number_format($totalGames ?? 24800) }} DUMPS EN LÍNEA</span>
            <span class="text-white/40">|</span>
            <span class="text-emerald-400">100% LIMPIO</span>
        </div>

        <!-- Main Headline -->
        <div class="space-y-3">
            <h1 class="text-3xl sm:text-5xl lg:text-5xl font-black text-[#18181B] dark:text-white tracking-tight font-sans">
                Centro de Comando <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#CE2D2D] via-red-500 to-amber-500">
                    para Emulación y ROMs.
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 max-w-xl mx-auto font-sans leading-relaxed">
                Descargas directas y sin engaños. Archivos verificados por la comunidad de preservación, sin ejecutables raros ni publicidad engañosa.
            </p>
        </div>

        <!-- Terminal Styled Search Input -->
        <div class="relative max-w-2xl mx-auto text-left"
             x-data="{
                 query: '',
                 results: [],
                 loading: false,
                 open: false,
                 timeout: null,
                 setSearch(text) {
                     this.query = text;
                     this.onInput();
                     this.$refs.terminalSearchInput?.focus();
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

            <form action="{{ route('search') }}" method="GET" class="p-2 rounded-2xl bg-white dark:bg-[#121215] border-2 border-[#1E1E1E] dark:border-[#CE2D2D]/60 shadow-[0_0_25px_rgba(206,45,45,0.12)] flex items-center gap-2 relative z-30">
                <div class="relative flex-1 flex items-center pl-3">
                    <span class="text-[#CE2D2D] font-mono font-bold mr-2 text-sm select-none">&gt;_</span>
                    <input 
                        type="text" 
                        name="q" 
                        x-ref="terminalSearchInput"
                        x-model="query"
                        @input="onInput()"
                        @focus="onFocus()"
                        autocomplete="off"
                        placeholder="Ejecutar búsqueda de juego (ej. Mario, Zelda, Crash, Halo)..." 
                        class="w-full bg-transparent border-0 px-2 py-2 text-xs sm:text-sm font-mono text-[#18181B] dark:text-emerald-400 placeholder-gray-400 dark:placeholder-gray-600 focus:outline-none"
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

                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-mono font-bold text-xs uppercase tracking-wider flex items-center gap-2 transition-all shrink-0 cursor-pointer shadow-md shadow-red-500/20 active:scale-95">
                    <span>EJECUTAR</span>
                    <i data-lucide="play" class="w-3 h-3 fill-current"></i>
                </button>
            </form>

            <!-- Dropdown Partial -->
            @include('web.heroes.live-search-dropdown')

            <!-- Quick terminal search triggers -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-mono pt-3">
                <span class="text-gray-400 dark:text-gray-500 text-[11px]">&gt; Populares:</span>
                @php
                    $popularQueries = ['Pokemon', 'Super Mario', 'Zelda', 'God of War', 'GTA', 'Resident Evil'];
                @endphp
                @foreach($popularQueries as $pq)
                    <button type="button" @click="setSearch('{{ $pq }}')" 
                            class="px-2 py-0.5 rounded bg-white dark:bg-[#1E1E24] hover:bg-[#CE2D2D] hover:text-white dark:hover:bg-[#CE2D2D] dark:hover:text-white border border-[#E5E0D8] dark:border-[#2E2E38] text-[11px] text-gray-700 dark:text-gray-300 transition-colors cursor-pointer">
                        {{ $pq }}
                    </button>
                @endforeach
            </div>

        </div>

    </div>

</section>
