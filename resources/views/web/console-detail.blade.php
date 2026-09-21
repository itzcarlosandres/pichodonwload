@extends('layouts.web')

@section('title', "Catálogo de ROMs e ISOs para {$console->name} — " . \App\Models\Setting::get('site_name', 'ROMHUB'))
@section('meta_description', "Descarga y explora juegos para {$console->name}. Todos los títulos verificados con hashes SHA-256 y CRC32.")

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-6 space-y-8">

    <!-- Breadcrumb Navigation -->
    <nav class="flex items-center gap-2 text-xs font-mono text-gray-500">
        <a href="{{ route('home') }}" class="hover:text-[#CE2D2D] transition-colors font-medium">INICIO</a>
        <span class="text-gray-400">/</span>
        <a href="{{ route('consoles.index') }}" class="hover:text-[#CE2D2D] uppercase transition-colors font-medium">CONSOLAS</a>
        <span class="text-gray-400">/</span>
        <span class="text-[#CE2D2D] font-bold uppercase">{{ $console->name }}</span>
    </nav>

    <!-- Header Console Info Card -->
    <div class="bg-white border-2 border-[#1E1E1E] rounded-3xl p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden shadow-sm">
        <div class="space-y-3 relative z-10 max-w-3xl">
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-lg bg-[#FAF7F2] text-[#18181B] border border-[#DDD6CB] font-mono text-[10px] font-bold uppercase">
                    {{ $console->manufacturer }}
                </span>
                @if($console->release_year)
                <span class="text-xs font-mono text-gray-500">Lanzamiento: <strong class="text-[#18181B]">{{ $console->release_year }}</strong></span>
                @endif
                @if($console->generation)
                <span class="text-gray-300">•</span>
                <span class="text-xs font-mono text-gray-500 font-bold">Gen {{ $console->generation }}</span>
                @endif
                @if($console->recommended_emulator)
                <span class="text-gray-300">•</span>
                <span class="text-xs font-mono text-[#CE2D2D] font-bold flex items-center gap-1">
                    <i data-lucide="cpu" class="w-3.5 h-3.5 text-[#CE2D2D]"></i> {{ $console->recommended_emulator }}
                </span>
                @endif
            </div>
            
            <h1 class="text-2xl sm:text-4xl font-black text-[#18181B] tracking-tight font-sans">
                {{ $console->name }}
            </h1>
            
            <p class="text-xs sm:text-sm text-gray-600 font-sans leading-relaxed">
                {{ $console->description ?: 'Bóveda de preservación oficial para títulos compatibles con los principales emuladores de ' . $console->name . '.' }}
            </p>
        </div>

        <div class="flex items-center gap-4 bg-[#FAF7F2] border border-[#DDD6CB] p-4 rounded-2xl text-center shrink-0 font-mono shadow-sm">
            <div class="px-2">
                <span class="text-2xl font-black text-[#18181B] block">{{ $games->total() }}</span>
                <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Juegos</span>
            </div>
            <div class="h-8 w-px bg-[#DDD6CB]"></div>
            <div class="px-2">
                <span class="text-2xl font-black text-[#CE2D2D] block">100%</span>
                <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Verificados</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar (Unified Capsule Bar) -->
    <form action="{{ route('consoles.show', $console->slug) }}" method="GET" class="p-3 sm:p-3.5 rounded-2xl bg-white border-2 border-[#1E1E1E] shadow-sm flex flex-wrap items-center justify-between gap-4 text-xs font-mono">
        <div class="flex flex-wrap items-center gap-3 flex-1">
            
            <!-- Category / Genre Select -->
            <select name="category" onchange="this.form.submit()" class="bg-[#FAF7F2] border border-[#DDD6CB] rounded-xl px-3 py-2 text-[#18181B] font-bold focus:outline-none focus:border-[#CE2D2D] transition-colors">
                <option value="">Todos los Géneros</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <!-- Region Select -->
            <select name="region" onchange="this.form.submit()" class="bg-[#FAF7F2] border border-[#DDD6CB] rounded-xl px-3 py-2 text-[#18181B] font-bold focus:outline-none focus:border-[#CE2D2D] transition-colors">
                <option value="">Todas las Regiones</option>
                <option value="USA" {{ request('region') === 'USA' ? 'selected' : '' }}>USA / NTSC-U</option>
                <option value="EUR" {{ request('region') === 'EUR' ? 'selected' : '' }}>Europa / PAL</option>
                <option value="JPN" {{ request('region') === 'JPN' ? 'selected' : '' }}>Japón / NTSC-J</option>
            </select>

            <!-- Sort Select -->
            <select name="sort" onchange="this.form.submit()" class="bg-[#FAF7F2] border border-[#DDD6CB] rounded-xl px-3 py-2 text-[#18181B] font-bold focus:outline-none focus:border-[#CE2D2D] transition-colors">
                <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Más Descargados</option>
                <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>Mejor Valorados</option>
                <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Últimas Adiciones</option>
                <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Alfabético (A-Z)</option>
            </select>
        </div>

        @if(request()->hasAny(['category', 'region', 'sort']))
        <a href="{{ route('consoles.show', $console->slug) }}" class="text-[#CE2D2D] hover:underline text-xs font-mono font-bold">
            Limpiar Filtros
        </a>
        @endif
    </form>

    <!-- Games Grid -->
    @if($games->isNotEmpty())
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @foreach($games as $game)
        <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1 hover:shadow-lg relative">
            
            <!-- Cover Aspect Ratio Container -->
            <div class="aspect-[3/4.1] bg-[#FAF7F2] relative overflow-hidden border-b border-[#E5E0D8]">
                <a href="{{ route('game.show', $game->slug) }}" class="block w-full h-full">
                    <img src="{{ $game->cover_thumb_url ?: $game->cover_url }}" 
                         alt="{{ $game->title }}" 
                         loading="lazy"
                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80';"
                         class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300">
                </a>
                
                <!-- Top Right Favorite Button -->
                <div class="absolute top-2 right-2 z-10">
                    <button 
                        @click="fetch('{{ route('favorites.toggle', $game->slug) }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                        }).then(r => r.json()).then(d => {
                            window.dispatchEvent(new CustomEvent('toast-notify', { detail: { message: d.message } }));
                        })"
                        class="w-7 h-7 rounded-xl bg-white/90 backdrop-blur border border-[#1E1E1E] text-gray-500 hover:text-[#CE2D2D] flex items-center justify-center transition-colors cursor-pointer shadow-sm"
                        title="Guardar en favoritos">
                        <i data-lucide="heart" class="w-3.5 h-3.5"></i>
                    </button>
                </div>

                <!-- Bottom Overlay: Size & Rating Pill -->
                <div class="console-card-badge absolute bottom-2 left-2 right-2 flex items-center justify-between z-10 pointer-events-none text-[10px] font-mono text-[#18181B] dark:text-white bg-white/95 dark:bg-[#18181B]/95 backdrop-blur px-2 py-1 rounded-lg border border-[#1E1E1E] dark:border-[#3F3F46] shadow-sm font-bold">
                    <span>{{ $game->formatted_size }}</span>
                    <span class="text-amber-500 font-bold flex items-center gap-0.5">★ {{ number_format($game->rating_average ?: 5.0, 1) }}</span>
                </div>
            </div>

            <!-- Info Body -->
            <div class="p-3.5 space-y-2.5 flex-1 flex flex-col justify-between">
                <div class="space-y-1.5">
                    <h3 class="card-game-title text-[13px] sm:text-sm font-black text-[#18181B] group-hover:text-[#CE2D2D] transition-colors line-clamp-1 leading-snug font-sans">
                        <a href="{{ route('game.show', $game->slug) }}">{{ $game->title }}</a>
                    </h3>

                    <!-- Badges / Funciones List -->
                    <div class="space-y-1 pt-1 min-h-[44px]">
                        @forelse($game->badges->take(2) as $badge)
                            <div class="flex items-center gap-1.5 text-[11px] font-mono font-bold tracking-tight" style="color: {{ $badge->text_color }}">
                                @if($badge->icon)
                                    <i data-lucide="{{ $badge->icon }}" class="w-3.5 h-3.5 shrink-0"></i>
                                @else
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                @endif
                                <span class="truncate">{{ $badge->name }}</span>
                            </div>
                        @empty
                            <div class="flex items-center gap-1.5 text-[11px] font-mono text-gray-500">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 shrink-0 text-[#CE2D2D]"></i>
                                <span>Verificado 1:1 Bit-Exact</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Footer Info: Region, Year, Download Button -->
                <div class="pt-2 border-t border-[#E5E0D8] flex items-center justify-between text-[11px] font-mono">
                    <span class="text-[10px] text-gray-500">{{ $game->region ?: 'Global' }} • {{ $game->release_year ?: 'Retro' }}</span>
                    
                    <a href="{{ route('game.download', $game->slug) }}" 
                       class="p-1.5 rounded-lg bg-[#CE2D2D] hover:bg-[#B71C1C] text-white border border-[#1E1E1E] transition-all flex items-center justify-center cursor-pointer shadow-sm"
                       title="Descargar">
                        <i data-lucide="download" class="w-3.5 h-3.5 stroke-[2]"></i>
                    </a>
                </div>
            </div>

        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="pt-6">
        {{ $games->links() }}
    </div>

    @else
    <div class="p-12 text-center bg-white border-2 border-dashed border-[#1E1E1E] rounded-2xl space-y-3">
        <i data-lucide="folder-search" class="w-8 h-8 text-gray-400 mx-auto"></i>
        <h3 class="text-sm font-bold text-[#18181B] font-sans">No se encontraron títulos con los filtros seleccionados</h3>
        <p class="text-xs text-gray-600 font-mono">Prueba cambiando el género, la región o limpiando los filtros.</p>
    </div>
    @endif

</main>
@endsection
