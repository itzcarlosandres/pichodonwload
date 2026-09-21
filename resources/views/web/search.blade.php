@extends('layouts.web')

@section('title', 'Explorador & Búsqueda Avanzada de Videojuegos — ' . \App\Models\Setting::get('site_name', 'ROMHUB'))
@section('meta_description', 'Explora, filtra y descarga títulos organizados por 20 consolas, géneros, regiones y tamaños verificados.')

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-8 space-y-8"
      x-data="{ 
          viewMode: localStorage.getItem('romhub_search_view') || 'grid',
          setView(mode) {
              this.viewMode = mode;
              localStorage.setItem('romhub_search_view', mode);
              $nextTick(() => { if (window.lucide) { lucide.createIcons(); } });
          }
      }">

    <!-- 1. Breadcrumbs & Vault Status -->
    <div class="flex flex-wrap items-center justify-between gap-3 text-xs font-mono">
        <nav class="flex items-center gap-2 text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-[#CE2D2D] transition-colors flex items-center gap-1 font-medium">
                <i data-lucide="home" class="w-3.5 h-3.5"></i>
                <span>Inicio</span>
            </a>
            <i data-lucide="chevron-right" class="w-3 h-3 text-gray-400"></i>
            <span class="text-[#18181B] font-bold">Explorador del Vault</span>
            @if(request('console'))
                @php $activeConsole = $consoles->firstWhere('slug', request('console')); @endphp
                @if($activeConsole)
                    <i data-lucide="chevron-right" class="w-3 h-3 text-gray-400"></i>
                    <span class="text-[#CE2D2D] font-bold">{{ $activeConsole->name }}</span>
                @endif
            @endif
        </nav>

        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-[#DDD6CB] text-[11px] text-[#18181B] font-mono shadow-sm">
            <span class="w-2 h-2 rounded-full bg-[#CE2D2D] animate-pulse"></span>
            <span>Vault Sincronizado • {{ $consoles->sum('games_count') }} ROMs totales</span>
        </div>
    </div>

    <!-- 2. Header Title & Quick Description -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-[#E5E0D8] pb-6">
        <div class="space-y-1.5">
            <div class="inline-flex items-center gap-1.5 text-xs font-mono font-bold text-[#CE2D2D] uppercase tracking-wider">
                <i data-lucide="database" class="w-4 h-4"></i>
                Catálogo General
            </div>
            <h1 class="text-2xl sm:text-4xl font-black text-[#18181B] tracking-tight font-sans">
                Explorador de Videojuegos & ROMs
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 font-sans">
                Mostrando <strong class="text-[#18181B] font-mono">{{ $games->total() }}</strong> títulos encontrados según tus criterios.
            </p>
        </div>

        <!-- View Mode Switcher (Grid / List) -->
        <div class="flex items-center gap-1.5 p-1 bg-white border border-[#DDD6CB] rounded-xl self-start md:self-auto shrink-0 shadow-sm">
            <button type="button" 
                    @click="setView('grid')" 
                    class="px-3 py-1.5 rounded-lg text-xs font-mono font-bold flex items-center gap-1.5 transition-all"
                    :class="viewMode === 'grid' ? 'bg-[#CE2D2D] text-white shadow-sm' : 'text-gray-600 hover:text-black'">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                <span>Cuadrícula</span>
            </button>
            <button type="button" 
                    @click="setView('list')" 
                    class="px-3 py-1.5 rounded-lg text-xs font-mono font-bold flex items-center gap-1.5 transition-all"
                    :class="viewMode === 'list' ? 'bg-[#CE2D2D] text-white shadow-sm' : 'text-gray-600 hover:text-black'">
                <i data-lucide="list" class="w-3.5 h-3.5"></i>
                <span>Lista / Tabla</span>
            </button>
        </div>
    </div>

    <!-- 3. Smart Filter & Search Box -->
    <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-4 sm:p-6 shadow-sm space-y-5">
        
        <form action="{{ route('search') }}" method="GET" class="space-y-4" id="search-filter-form">
            
            <!-- Main Search Input Bar with LIVE AUTOCOMPLETE -->
            <div class="relative"
                 x-data="{
                     query: '{{ addslashes(request('q', '')) }}',
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
                
                <div class="relative flex items-center">
                    <i data-lucide="search" class="w-5 h-5 text-gray-400 absolute left-4 pointer-events-none"></i>
                    <input 
                        type="text" 
                        name="q" 
                        id="search-q-input"
                        x-model="query"
                        @input="onInput()"
                        @focus="onFocus()"
                        autocomplete="off"
                        placeholder="Buscar por título, desarrollador, editor, saga o código serial (ej. SLUS-20065)..." 
                        class="w-full bg-[#FAF7F2] border-2 border-[#1E1E1E] focus:border-[#CE2D2D] rounded-xl pl-12 pr-12 py-3 text-sm text-[#18181B] placeholder-gray-400 focus:outline-none font-sans font-medium transition-all"
                    >
                    <div class="absolute right-3.5 flex items-center gap-1.5">
                        <template x-if="loading">
                            <div class="w-4 h-4 border-2 border-[#CE2D2D] border-t-transparent rounded-full animate-spin"></div>
                        </template>
                        <template x-if="query.length > 0 && !loading">
                            <button type="button" @click="query = ''; results = []; open = false" class="p-1 rounded-lg text-gray-400 hover:text-black hover:bg-black/5 transition-colors" title="Limpiar texto">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- LIVE DROPDOWN RESULTS -->
                <div x-show="open" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-98"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-98"
                     class="absolute left-0 right-0 top-full mt-2 bg-white border-2 border-[#1E1E1E] rounded-2xl shadow-[0_20px_40px_rgba(0,0,0,0.15)] overflow-hidden z-50">
                    
                    <!-- Header -->
                    <div class="px-4 py-2 bg-[#FAF7F2] border-b border-[#E5E0D8] flex items-center justify-between text-xs font-mono">
                        <span class="font-bold text-[#18181B] flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#CE2D2D] animate-pulse"></span>
                            <span x-text="loading ? 'Buscando en vivo...' : (results.length > 0 ? 'Sugerencias rápidas (' + results.length + ')' : 'Sin resultados rápidos')"></span>
                        </span>
                        <span class="text-gray-500 text-[11px] font-sans" x-show="results.length > 0">Pulsa Enter para filtrar la página</span>
                    </div>

                    <!-- List -->
                    <div class="max-h-[320px] overflow-y-auto divide-y divide-[#E5E0D8]">
                        <template x-for="item in results" :key="item.id">
                            <a :href="item.url" 
                               class="flex items-center gap-3.5 p-3 hover:bg-[#FAF7F2] transition-colors group">
                                <div class="w-10 h-13 bg-[#EDE7DE] rounded-lg border border-[#DDD6CB] overflow-hidden shrink-0 flex items-center justify-center">
                                    <img :src="item.cover_url" :alt="item.title" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80';" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-[#F5EFE6] text-[#CE2D2D] border border-[#DDD6CB]" x-text="item.console"></span>
                                        <template x-if="item.region">
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-gray-100 text-gray-600 uppercase" x-text="item.region"></span>
                                        </template>
                                    </div>
                                    <h4 class="text-xs sm:text-sm font-bold text-[#18181B] group-hover:text-[#CE2D2D] truncate transition-colors font-sans" x-text="item.title"></h4>
                                    <div class="flex items-center gap-3 text-[11px] font-mono text-gray-500 mt-0.5">
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
                                    </div>
                                </div>
                                <div class="w-8 h-8 rounded-lg bg-[#FAF7F2] group-hover:bg-[#CE2D2D] group-hover:text-white border border-[#DDD6CB] group-hover:border-[#CE2D2D] flex items-center justify-center text-gray-500 transition-all shrink-0">
                                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-0.5 transition-transform"></i>
                                </div>
                            </a>
                        </template>

                        <template x-if="!loading && results.length === 0 && query.trim().length >= 2">
                            <div class="p-5 text-center space-y-1">
                                <p class="text-xs font-bold text-[#18181B] font-sans">No hay sugerencias directas para "<span x-text="query"></span>"</p>
                                <p class="text-[11px] text-gray-500 font-sans">Presiona "Aplicar Filtros" o Enter para buscar coincidencias parciales.</p>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Faceted Select Filters Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs font-mono">
                
                <!-- Consola -->
                <div>
                    <label class="block text-gray-600 mb-1.5 text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="gamepad-2" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                        <span>Consola / Sistema</span>
                    </label>
                    <select name="console" 
                            onchange="document.getElementById('search-filter-form').submit()"
                            class="w-full bg-[#FAF7F2] border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-xl px-3 py-2.5 text-[#18181B] font-bold focus:outline-none cursor-pointer transition-colors">
                        <option value="">Todas las Consolas ({{ $consoles->count() }})</option>
                        @foreach($consoles as $con)
                            <option value="{{ $con->slug }}" {{ request('console') === $con->slug ? 'selected' : '' }}>
                                {{ $con->name }} ({{ $con->games_count }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Género / Categoría -->
                <div>
                    <label class="block text-gray-600 mb-1.5 text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="tag" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                        <span>Género / Categoría</span>
                    </label>
                    <select name="category" 
                            onchange="document.getElementById('search-filter-form').submit()"
                            class="w-full bg-[#FAF7F2] border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-xl px-3 py-2.5 text-[#18181B] font-bold focus:outline-none cursor-pointer transition-colors">
                        <option value="">Todos los Géneros ({{ $categories->count() }})</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>
                                {{ $cat->name }} ({{ $cat->games_count }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Región -->
                <div>
                    <label class="block text-gray-600 mb-1.5 text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                        <span>Región / Formato</span>
                    </label>
                    <select name="region" 
                            onchange="document.getElementById('search-filter-form').submit()"
                            class="w-full bg-[#FAF7F2] border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-xl px-3 py-2.5 text-[#18181B] font-bold focus:outline-none cursor-pointer transition-colors">
                        <option value="">Todas las Regiones</option>
                        <option value="USA" {{ request('region') === 'USA' ? 'selected' : '' }}>USA / NTSC-U</option>
                        <option value="EUR" {{ request('region') === 'EUR' ? 'selected' : '' }}>Europa / PAL</option>
                        <option value="JPN" {{ request('region') === 'JPN' ? 'selected' : '' }}>Japón / NTSC-J</option>
                        <option value="GLOBAL" {{ request('region') === 'GLOBAL' ? 'selected' : '' }}>Global / World</option>
                    </select>
                </div>

                <!-- Ordenar por -->
                <div>
                    <label class="block text-gray-600 mb-1.5 text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="arrow-down-up" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                        <span>Criterio de Orden</span>
                    </label>
                    <select name="sort" 
                            onchange="document.getElementById('search-filter-form').submit()"
                            class="w-full bg-[#FAF7F2] border border-[#DDD6CB] focus:border-[#CE2D2D] rounded-xl px-3 py-2.5 text-[#18181B] font-bold focus:outline-none cursor-pointer transition-colors">
                        <option value="popular" {{ request('sort', 'popular') === 'popular' ? 'selected' : '' }}>Más Populares / Descargas</option>
                        <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Mayor Calificación (★)</option>
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Agregados Recientemente</option>
                        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Alfabético (A - Z)</option>
                    </select>
                </div>

            </div>

            <!-- Action Bar: Active filter pills & Submit button -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-[#E5E0D8]">
                
                <!-- Active Filters Chips -->
                <div class="flex flex-wrap items-center gap-2">
                    @php 
                        $hasFilters = request()->hasAny(['q', 'console', 'category', 'region', 'sort']) && (request('q') || request('console') || request('category') || request('region') || (request('sort') && request('sort') !== 'popular'));
                    @endphp

                    @if($hasFilters)
                        <span class="text-[11px] font-mono font-bold text-gray-500">Filtros activos:</span>
                        
                        @if(request('q'))
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#FDF2F2] text-[#CE2D2D] border border-[#FCA5A5] text-xs font-mono font-bold">
                                <span>Texto: "{{ request('q') }}"</span>
                                <a href="{{ route('search', request()->except('q')) }}" class="text-gray-500 hover:text-black">&times;</a>
                            </span>
                        @endif

                        @if(request('console'))
                            @php $conObj = $consoles->firstWhere('slug', request('console')); @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#FDF2F2] text-[#CE2D2D] border border-[#FCA5A5] text-xs font-mono font-bold">
                                <span>Consola: {{ $conObj ? $conObj->name : request('console') }}</span>
                                <a href="{{ route('search', request()->except('console')) }}" class="text-gray-500 hover:text-black">&times;</a>
                            </span>
                        @endif

                        @if(request('category'))
                            @php $catObj = $categories->firstWhere('slug', request('category')); @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#FDF2F2] text-[#CE2D2D] border border-[#FCA5A5] text-xs font-mono font-bold">
                                <span>Género: {{ $catObj ? $catObj->name : request('category') }}</span>
                                <a href="{{ route('search', request()->except('category')) }}" class="text-gray-500 hover:text-black">&times;</a>
                            </span>
                        @endif

                        @if(request('region'))
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#FDF2F2] text-[#CE2D2D] border border-[#FCA5A5] text-xs font-mono font-bold">
                                <span>Región: {{ request('region') }}</span>
                                <a href="{{ route('search', request()->except('region')) }}" class="text-gray-500 hover:text-black">&times;</a>
                            </span>
                        @endif

                        <a href="{{ route('search') }}" class="text-xs font-mono text-[#CE2D2D] hover:underline ml-1 font-bold">
                            Restablecer todos
                        </a>
                    @else
                        <span class="text-[11px] font-mono text-gray-500">
                            Explorando catálogo completo sin filtros adicionales
                        </span>
                    @endif
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-black text-xs font-sans uppercase tracking-wider flex items-center gap-2 transition-all shadow-md shadow-red-500/20 cursor-pointer shrink-0">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Aplicar Filtros</span>
                </button>

            </div>

        </form>

    </div>

    <!-- 4. Quick Consoles Chips Carousel (Touch edge-to-edge scroll on mobile) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 -mx-4 px-4 sm:mx-0 sm:px-0 scrollbar-none text-xs font-mono">
        <a href="{{ route('search', request()->except('console')) }}" 
           class="px-3.5 py-1.5 rounded-xl whitespace-nowrap transition-all border shrink-0 flex items-center gap-1.5 active:scale-95 {{ !request('console') ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white hover:bg-[#FAF7F2] text-gray-700 border-[#DDD6CB]' }}">
            <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
            <span>Todas ({{ $consoles->count() }})</span>
        </a>

        @foreach($consoles as $con)
            @php $isSelected = (request('console') === $con->slug); @endphp
            <a href="{{ route('search', array_merge(request()->query(), ['console' => $con->slug])) }}" 
               class="px-3.5 py-1.5 rounded-xl whitespace-nowrap transition-all border shrink-0 flex items-center gap-1.5 active:scale-95 {{ $isSelected ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white hover:bg-[#FAF7F2] text-gray-700 border-[#DDD6CB]' }}">
                <span>{{ $con->short_name ?: $con->name }}</span>
                <span class="text-[10px] {{ $isSelected ? 'text-white/80' : 'text-gray-500' }}">({{ $con->games_count }})</span>
            </a>
        @endforeach
    </div>

    <!-- 5. Results Area -->
    @if($games->isNotEmpty())
        
        <!-- A. GRID VIEW (Default) -->
        <div x-show="viewMode === 'grid'" 
             class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($games as $game)
                <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1 hover:shadow-lg relative">
                    
                    <!-- Thumbnail Aspect Box -->
                    <div class="aspect-[3/4.1] bg-[#FAF7F2] relative overflow-hidden border-b border-[#E5E0D8]">
                        <a href="{{ route('game.show', $game->slug) }}" class="block w-full h-full">
                            <img src="{{ $game->cover_thumb_url ?: $game->cover_url }}" 
                                 alt="{{ $game->title }}" 
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80';"
                                 class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300">
                        </a>
                        
                        <!-- Top Floating Console Pill -->
                        <div class="absolute top-2 left-2 right-2 flex items-center justify-between z-10">
                            <span class="console-card-badge px-2 py-0.5 rounded-md bg-white/95 dark:bg-[#18181B]/95 text-[#18181B] dark:text-white border border-[#1E1E1E] dark:border-[#3F3F46] shadow-sm text-[10px] font-mono font-black backdrop-blur">
                                {{ $game->console->short_name ?: $game->console->name }}
                            </span>
                            
                            @if($game->region)
                                <span class="px-1.5 py-0.5 rounded bg-[#FAF7F2]/90 dark:bg-[#272730]/90 backdrop-blur text-[9px] font-mono font-bold text-gray-600 dark:text-gray-300 border border-[#DDD6CB] dark:border-[#3F3F46]">
                                    {{ $game->region }}
                                </span>
                            @endif
                        </div>

                        <!-- Hover Overlay with Quick Download Action -->
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
                            <h3 class="card-game-title text-[13px] sm:text-sm font-black text-[#18181B] group-hover:text-[#CE2D2D] transition-colors line-clamp-1 leading-snug font-sans tracking-tight mt-0.5">
                                <a href="{{ route('game.show', $game->slug) }}">{{ $game->title }}</a>
                            </h3>
                        </div>

                        <!-- Footer Meta -->
                        <div class="pt-2 border-t border-[#E5E0D8] flex items-center justify-between text-[11px] font-mono">
                            <span class="text-[10px] text-gray-500 font-semibold">{{ $game->formatted_size }}</span>
                            <span class="text-amber-500 font-bold flex items-center gap-0.5">
                                <span>★</span>
                                <span>{{ number_format($game->rating_average, 1) }}</span>
                            </span>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <!-- B. LIST / TABLE VIEW -->
        <div x-show="viewMode === 'list'" x-cloak class="space-y-2.5">
            @foreach($games as $index => $game)
                <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl p-3.5 sm:p-4 flex items-center justify-between gap-4 transition-all group hover:shadow-md">
                    
                    <!-- Left: Cover/Title & Meta -->
                    <div class="flex items-center gap-3.5 min-w-0 flex-1">
                        
                        <!-- Thumbnail Cover -->
                        <a href="{{ route('game.show', $game->slug) }}" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-[#FAF7F2] overflow-hidden border border-[#E5E0D8] shrink-0 block aspect-square group-hover:scale-105 transition-transform">
                            <img src="{{ $game->cover_thumb_url ?: $game->cover_url }}" 
                                 alt="{{ $game->title }}" 
                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80';"
                                 class="w-full h-full object-cover">
                        </a>

                        <!-- Title and Metadata -->
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('game.show', $game->slug) }}" class="font-black text-[#18181B] group-hover:text-[#CE2D2D] text-sm sm:text-base transition-colors truncate block font-sans">
                                    {{ $game->title }}
                                </a>
                                @if($game->badges->isNotEmpty())
                                    @php $badge = $game->badges->first(); @endphp
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold" style="background-color: {{ $badge->bg_color }}; color: {{ $badge->text_color }}; border: 1px solid {{ $badge->border_color }}">
                                        {{ $badge->name }}
                                    </span>
                                @endif
                            </div>

                            <!-- Meta details row -->
                            <div class="flex flex-wrap items-center gap-2 text-[11px] text-gray-500 font-mono mt-1">
                                <span class="px-2 py-0.5 rounded-md bg-[#EDE7DE] text-[#18181B] border border-[#DDD6CB] text-[10px] font-bold">
                                    {{ $game->console->name }}
                                </span>
                                <span>•</span>
                                <span>{{ $game->region ?: 'Global' }}</span>
                                <span>•</span>
                                <span class="text-[#18181B] font-bold">{{ $game->formatted_size }}</span>
                                @if($game->developer)
                                    <span>•</span>
                                    <span class="text-gray-500 truncate max-w-[150px]">{{ $game->developer }}</span>
                                @endif
                                <span>•</span>
                                <span class="text-amber-500 font-sans font-bold flex items-center gap-0.5">
                                    <span>★</span>
                                    <span>{{ number_format($game->rating_average, 1) }}</span>
                                </span>
                            </div>
                        </div>

                    </div>

                    <!-- Right Action Button -->
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('game.show', $game->slug) }}" 
                           class="px-4 py-2 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white border border-[#1E1E1E] text-xs font-black font-sans flex items-center gap-1.5 transition-all shadow-sm">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Descargar</span>
                        </a>
                    </div>

                </div>
            @endforeach
        </div>

        <!-- 6. Styled Pagination Container -->
        <div class="pt-6 border-t border-[#E5E0D8] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs font-mono text-gray-600">
            <div>
                Mostrando <span class="text-[#18181B] font-bold">{{ $games->firstItem() ?? 0 }}</span> - <span class="text-[#18181B] font-bold">{{ $games->lastItem() ?? 0 }}</span> de <span class="text-[#CE2D2D] font-bold">{{ $games->total() }}</span> ROMs
            </div>
            
            <div class="custom-pagination">
                {{ $games->links() }}
            </div>
        </div>

    @else
        <!-- No Results Empty State -->
        <div class="p-12 sm:p-16 text-center bg-white border-2 border-[#1E1E1E] rounded-3xl space-y-5 max-w-2xl mx-auto shadow-sm">
            <div class="w-16 h-16 rounded-2xl bg-[#FDF2F2] border border-[#FCA5A5] text-[#CE2D2D] flex items-center justify-center mx-auto shadow-sm">
                <i data-lucide="search-x" class="w-8 h-8"></i>
            </div>
            
            <div class="space-y-2">
                <h3 class="text-lg sm:text-xl font-black text-[#18181B] font-sans">
                    No se encontraron videojuegos coincidentes
                </h3>
                <p class="text-xs sm:text-sm text-gray-600 font-sans max-w-md mx-auto leading-relaxed">
                    No encontramos títulos con los filtros actuales. Intenta simplificar la búsqueda o seleccionar otra plataforma.
                </p>
            </div>

            <!-- Quick Suggestions -->
            <div class="pt-2 flex flex-wrap items-center justify-center gap-2">
                <a href="{{ route('search') }}" class="px-4 py-2 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white text-xs font-mono font-bold transition-all shadow-sm">
                    Ver todos los títulos ({{ $consoles->sum('games_count') }})
                </a>
                <a href="{{ route('consoles.index') }}" class="px-4 py-2 rounded-xl bg-white hover:bg-[#FAF7F2] text-[#18181B] border border-[#1E1E1E] text-xs font-mono font-bold transition-all">
                    Explorar por Consolas
                </a>
            </div>
        </div>
    @endif

</main>
@endsection
