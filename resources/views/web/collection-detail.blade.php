@extends('layouts.web')

@section('title', "Colección {$franchise->name} — Descargar ROMs y Videojuegos | " . \App\Models\Setting::get('site_name', 'ROMHUB'))
@section('meta_description', "Descarga todos los títulos de la saga {$franchise->name} para PlayStation, Nintendo, Sega y Xbox con volcados limpios verificados.")

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-8 space-y-8">

    <!-- Breadcrumbs -->
    <nav class="flex items-center gap-2 text-xs font-mono text-gray-500">
        <a href="{{ route('home') }}" class="hover:text-[#CE2D2D] transition-colors font-medium">INICIO</a>
        <span class="text-gray-400">/</span>
        <a href="{{ route('collections.index') }}" class="hover:text-[#CE2D2D] transition-colors font-medium">COLECCIONES</a>
        <span class="text-gray-400">/</span>
        <span class="text-[#CE2D2D] font-bold uppercase">{{ $franchise->name }}</span>
    </nav>

    <!-- Header Section -->
    <section class="space-y-4">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#CE2D2D] text-white text-xs font-mono font-bold shadow-sm">
            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
            <span>Saga Legendaria • {{ $games->total() }} Títulos Disponibles</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-[#E5E0D8] pb-6">
            <div class="space-y-2 max-w-2xl">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-white shadow-md shrink-0"
                         style="background-color: {{ $franchise->color ?: '#CE2D2D' }};">
                        <i data-lucide="{{ $franchise->icon ?: 'sparkles' }}" class="w-6 h-6"></i>
                    </div>
                    <h1 class="text-3xl sm:text-5xl font-black text-[#18181B] tracking-tight font-sans">
                        {{ $franchise->name }}
                    </h1>
                </div>
                <p class="text-xs sm:text-sm text-gray-600 font-sans leading-relaxed">
                    {{ $franchise->subtitle }}. Explora y descarga todos los lanzamientos canónicos, spin-offs y entregas remasterizadas preservadas en el Vault.
                </p>
            </div>

            <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-white border-2 border-[#1E1E1E] font-mono shrink-0 shadow-sm">
                <div class="text-center px-2">
                    <span class="text-xl sm:text-2xl font-black text-[#CE2D2D] block">{{ $games->total() }}</span>
                    <span class="text-[10px] text-gray-500 uppercase tracking-wider font-bold">ROMs en Vault</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Games Grid for this Collection -->
    @if($games->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($games as $game)
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

                        <!-- Top Floating Console Pill -->
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

                        <!-- Size Pill on Bottom-Left (Fades on hover) -->
                        <div class="absolute bottom-2.5 left-2.5 z-10 px-2 py-0.5 rounded-md bg-black/75 backdrop-blur-md text-[10px] font-mono text-gray-200 border border-white/10 group-hover:opacity-0 transition-opacity duration-200 pointer-events-none">
                            {{ $game->formatted_size }}
                        </div>

                        <!-- Rating Pill on Bottom-Right (Fades on hover) -->
                        <div class="absolute bottom-2.5 right-2.5 z-10 px-2 py-0.5 rounded-md bg-black/75 backdrop-blur-md text-[10px] font-mono text-amber-400 font-bold border border-white/10 flex items-center gap-1 group-hover:opacity-0 transition-opacity duration-200 pointer-events-none">
                            <span>★</span>
                            <span>{{ number_format($game->rating_average ?: 5.0, 1) }}</span>
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
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pt-6 border-t border-[#E5E0D8] flex justify-center">
            {{ $games->links() }}
        </div>
    @else
        <div class="p-12 text-center bg-white border-2 border-[#1E1E1E] rounded-3xl space-y-3 max-w-xl mx-auto shadow-sm">
            <i data-lucide="search-x" class="w-8 h-8 text-gray-400 mx-auto"></i>
            <h3 class="text-sm font-mono font-bold text-[#18181B] uppercase">Próximamente más títulos</h3>
            <p class="text-xs text-gray-600 font-sans">Estamos catalogando más entregas de esta saga. Puedes buscar títulos específicos en el explorador general.</p>
            <a href="{{ route('search', ['q' => $franchise['query']]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#CE2D2D] text-white text-xs font-mono font-bold hover:bg-[#B71C1C] transition-colors">
                <span>Buscar coincidencias amplias</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    @endif

</main>
@endsection
