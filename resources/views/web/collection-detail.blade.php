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
                <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1 hover:shadow-lg relative">
                    
                    <!-- Thumbnail Aspect Box -->
                    <div class="aspect-[3/4.1] bg-[#FAF7F2] relative overflow-hidden border-b border-[#E5E0D8]">
                        <a href="{{ route('game.show', $game->slug) }}" class="block w-full h-full">
                            <img src="{{ $game->cover_thumb_url ?: $game->cover_url }}" 
                                 alt="{{ $game->title }}" 
                                 loading="lazy"
                                 class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-300">
                        </a>
                        
                        <!-- Top Floating Console Pill -->
                        <div class="absolute top-2 left-2 z-10">
                            <span class="console-card-badge px-2 py-0.5 rounded-md bg-white/95 dark:bg-[#18181B]/95 text-[#18181B] dark:text-white border border-[#1E1E1E] dark:border-[#3F3F46] shadow-sm text-[10px] font-mono font-black backdrop-blur">
                                {{ $game->console->short_name ?: $game->console->name }}
                            </span>
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
                                <span>{{ number_format($game->rating_average ?: 5.0, 1) }}</span>
                            </span>
                        </div>
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
