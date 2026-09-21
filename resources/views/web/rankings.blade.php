@extends('layouts.web')

@section('title', 'Top 25 Videojuegos Más Jugados' . ($activeConsole ? ' de ' . $activeConsole->name : '') . ' | ' . \App\Models\Setting::get('site_name', 'ROMHUB'))
@section('meta_description', 'Descubre el ranking de los 25 mejores videojuegos retro y clásicos preservados con mayor puntuación y descargas de la comunidad.')

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-6 space-y-8">

    <!-- Breadcrumb Nav -->
    <nav class="flex items-center gap-2 text-xs font-mono text-gray-500 dark:text-gray-400">
        <a href="{{ route('home') }}" class="hover:text-[#CE2D2D] transition-colors font-medium">INICIO</a>
        <span class="text-gray-400 dark:text-gray-600">/</span>
        <span class="text-[#18181B] dark:text-white font-bold uppercase">RANKINGS TOP 25</span>
        @if($activeConsole)
            <span class="text-gray-400 dark:text-gray-600">/</span>
            <span class="text-[#CE2D2D] font-bold uppercase">{{ $activeConsole->name }}</span>
        @endif
    </nav>

    <!-- Header Hero Banner -->
    <section class="relative overflow-hidden rounded-3xl bg-white dark:bg-[#18181B] border-2 border-[#1E1E1E] dark:border-[#27272A] p-6 sm:p-10 shadow-sm">
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="space-y-2.5 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#CE2D2D] text-white text-xs font-mono font-bold shadow-sm">
                    <i data-lucide="trophy" class="w-3.5 h-3.5"></i>
                    <span>Hall de la Fama • Vault Legends</span>
                </div>
                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black text-[#18181B] dark:text-white tracking-tight font-sans">
                    Top 25 Más Jugados {{ $activeConsole ? 'de ' . $activeConsole->name : 'del Vault' }}
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 font-sans leading-relaxed">
                    Las 25 obras maestras con mayor récord de descargas y mejores calificaciones otorgadas por la comunidad de emulación.
                </p>
            </div>

            <!-- Total Games & Fast Stats -->
            <div class="flex items-center gap-3 bg-[#FAF7F2] dark:bg-[#202025] border border-[#DDD6CB] dark:border-[#27272A] p-3 rounded-2xl shrink-0">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 border border-amber-500/30 text-amber-500 dark:text-amber-400 flex items-center justify-center font-black text-xl font-mono shadow-sm">
                    #1
                </div>
                <div class="text-xs font-mono">
                    <span class="text-gray-500 dark:text-gray-400 block uppercase text-[10px] font-bold">Top Título</span>
                    <strong class="text-[#18181B] dark:text-white block truncate max-w-[150px]">{{ $topGames->first()?->title ?? 'N/A' }}</strong>
                    <span class="text-[#CE2D2D] dark:text-[#EF4444] font-bold text-[11px]">{{ number_format($topGames->first()?->download_count ?? 0) }} descargas</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Console Filter Pills Bar (Touch horizontal scroll) -->
    <section class="space-y-3">
        <div class="flex items-center justify-between border-b border-[#E5E0D8] dark:border-[#27272A] pb-2 text-xs font-mono">
            <span class="font-bold uppercase text-[#18181B] dark:text-white flex items-center gap-1.5">
                <i data-lucide="filter" class="w-3.5 h-3.5 text-[#CE2D2D]"></i>
                Filtrar por Consola
            </span>
            
            <!-- Sort dropdown / links -->
            <div class="flex items-center gap-2">
                <span class="text-gray-500 dark:text-gray-400 text-[11px]">Ordenar:</span>
                <a href="{{ route('rankings', array_merge(request()->query(), ['sort' => 'downloads'])) }}" 
                   class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition-colors {{ $sortBy === 'downloads' ? 'bg-[#CE2D2D] text-white border-[#CE2D2D]' : 'bg-white dark:bg-[#18181B] text-gray-700 dark:text-gray-300 border-[#DDD6CB] dark:border-[#27272A] hover:bg-[#FAF7F2] dark:hover:bg-[#202025]' }}">
                    Descargas
                </a>
                <a href="{{ route('rankings', array_merge(request()->query(), ['sort' => 'rating'])) }}" 
                   class="px-2.5 py-1 rounded-lg border text-[11px] font-bold transition-colors {{ $sortBy === 'rating' ? 'bg-[#CE2D2D] text-white border-[#CE2D2D]' : 'bg-white dark:bg-[#18181B] text-gray-700 dark:text-gray-300 border-[#DDD6CB] dark:border-[#27272A] hover:bg-[#FAF7F2] dark:hover:bg-[#202025]' }}">
                    ★ Rating
                </a>
            </div>
        </div>

        <div class="flex items-center gap-2 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 scrollbar-none text-xs font-mono">
            <!-- ALL CONSOLES PILL -->
            <a href="{{ route('rankings', ['sort' => $sortBy]) }}" 
               class="px-3.5 py-1.5 rounded-xl whitespace-nowrap transition-all border shrink-0 flex items-center gap-1.5 active:scale-95 {{ empty($selectedConsole) ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white dark:bg-[#18181B] hover:bg-[#FAF7F2] dark:hover:bg-[#202025] text-gray-700 dark:text-gray-300 border-[#DDD6CB] dark:border-[#27272A]' }}">
                <span>Todos los Sistemas</span>
            </a>

            @foreach($consoles as $con)
                @php $isSelected = ($selectedConsole === $con->slug); @endphp
                <a href="{{ route('rankings', ['console' => $con->slug, 'sort' => $sortBy]) }}" 
                   class="px-3.5 py-1.5 rounded-xl whitespace-nowrap transition-all border shrink-0 flex items-center gap-1.5 active:scale-95 {{ $isSelected ? 'bg-[#CE2D2D] text-white border-[#CE2D2D] font-bold shadow-sm' : 'bg-white dark:bg-[#18181B] hover:bg-[#FAF7F2] dark:hover:bg-[#202025] text-gray-700 dark:text-gray-300 border-[#DDD6CB] dark:border-[#27272A]' }}">
                    <span>{{ $con->short_name ?: $con->name }}</span>
                    <span class="text-[10px] {{ $isSelected ? 'text-white/80' : 'text-gray-400 dark:text-gray-500' }}">({{ $con->games_count }})</span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- Top 3 Podium Cards (If at least 3 games exist) -->
    @if($topGames->count() >= 3)
    <section class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 pt-2">
        
        <!-- #2 Silver Medal -->
        @php $silver = $topGames->get(1); @endphp
        @if($silver)
        <div class="bg-white dark:bg-[#18181B] border-2 border-[#1E1E1E] dark:border-[#27272A] rounded-3xl p-5 relative flex flex-col justify-between shadow-sm hover:shadow-md transition-all group order-2 md:order-1">
            <div class="absolute -top-3 left-5 px-3 py-1 rounded-full bg-slate-200 dark:bg-slate-700 border-2 border-slate-400 dark:border-slate-500 text-slate-800 dark:text-slate-100 text-xs font-mono font-black shadow-sm flex items-center gap-1.5">
                <span>🥈 #02</span>
                <span class="text-[10px] font-bold">PLATA</span>
            </div>

            <div class="space-y-4 pt-2">
                <div class="aspect-[3/4] rounded-2xl overflow-hidden bg-[#FAF7F2] dark:bg-[#202025] border border-[#DDD6CB] dark:border-[#27272A] relative">
                    <img src="{{ $silver->cover_url ?: asset('images/default-cover.webp') }}" alt="{{ $silver->title }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80';" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 right-2 px-2 py-0.5 rounded-lg bg-black/80 backdrop-blur text-white text-[10px] font-mono font-bold">
                        {{ $silver->console->short_name ?: $silver->console->name }}
                    </div>
                </div>

                <div>
                    <h3 class="text-base font-black text-[#18181B] dark:text-white group-hover:text-[#CE2D2D] dark:group-hover:text-[#EF4444] transition-colors truncate">
                        {{ $silver->title }}
                    </h3>
                    <div class="flex items-center justify-between text-xs font-mono text-gray-500 dark:text-gray-400 mt-1">
                        <span class="text-amber-500 font-bold">★ {{ number_format($silver->rating_average ?: 5.0, 1) }}</span>
                        <span class="font-bold text-[#18181B] dark:text-white">{{ number_format($silver->download_count) }} descargas</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('game.show', $silver->slug) }}" class="mt-4 w-full py-2.5 rounded-xl bg-[#18181B] dark:bg-[#272730] hover:bg-[#CE2D2D] dark:hover:bg-[#CE2D2D] text-white text-xs font-mono font-bold text-center transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                <span>Ver Ficha & ROM</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
        @endif

        <!-- #1 Gold Medal (Main Hero Podium) -->
        @php $gold = $topGames->get(0); @endphp
        @if($gold)
        <div class="bg-gradient-to-b from-[#FFFDF5] to-white dark:from-[#251D08] dark:to-[#18181B] border-2 border-[#EAB308] rounded-3xl p-6 relative flex flex-col justify-between shadow-lg shadow-amber-500/10 hover:shadow-xl transition-all group order-1 md:order-2 md:-translate-y-3">
            <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1.5 rounded-full bg-[#EAB308] text-white text-xs font-mono font-black shadow-md flex items-center gap-1.5 border-2 border-white dark:border-[#18181B]">
                <i data-lucide="crown" class="w-4 h-4 fill-white text-white"></i>
                <span>👑 #01 CAMPEÓN DEL VAULT</span>
            </div>

            <div class="space-y-4 pt-3">
                <div class="aspect-[3/4] rounded-2xl overflow-hidden bg-[#FAF7F2] dark:bg-[#202025] border-2 border-[#EAB308]/40 relative shadow-inner">
                    <img src="{{ $gold->cover_url ?: asset('images/default-cover.webp') }}" alt="{{ $gold->title }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80';" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 right-2 px-2.5 py-1 rounded-lg bg-[#CE2D2D] text-white text-[10px] font-mono font-black shadow-sm">
                        {{ $gold->console->short_name ?: $gold->console->name }}
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-black text-[#18181B] dark:text-white group-hover:text-[#CE2D2D] dark:group-hover:text-[#EF4444] transition-colors truncate text-center">
                        {{ $gold->title }}
                    </h3>
                    <div class="flex items-center justify-center gap-4 text-xs font-mono text-gray-500 dark:text-gray-400 mt-1.5">
                        <span class="text-amber-500 font-bold flex items-center gap-1">
                            <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400 text-amber-400"></i>
                            <span>{{ number_format($gold->rating_average ?: 5.0, 1) }}</span>
                        </span>
                        <span>•</span>
                        <span class="font-black text-[#CE2D2D] dark:text-[#EF4444]">{{ number_format($gold->download_count) }} descargas</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('game.show', $gold->slug) }}" class="mt-4 w-full py-3 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white text-xs font-mono font-black uppercase tracking-wider text-center transition-all flex items-center justify-center gap-2 shadow-md shadow-red-500/25 active:scale-95">
                <i data-lucide="download" class="w-4 h-4 stroke-[2.5]"></i>
                <span>Descargar #1 Oficial</span>
            </a>
        </div>
        @endif

        <!-- #3 Bronze Medal -->
        @php $bronze = $topGames->get(2); @endphp
        @if($bronze)
        <div class="bg-white dark:bg-[#18181B] border-2 border-[#1E1E1E] dark:border-[#27272A] rounded-3xl p-5 relative flex flex-col justify-between shadow-sm hover:shadow-md transition-all group order-3">
            <div class="absolute -top-3 left-5 px-3 py-1 rounded-full bg-amber-700 text-white text-xs font-mono font-black shadow-sm flex items-center gap-1.5 border border-amber-800">
                <span>🥉 #03</span>
                <span class="text-[10px] font-bold">BRONCE</span>
            </div>

            <div class="space-y-4 pt-2">
                <div class="aspect-[3/4] rounded-2xl overflow-hidden bg-[#FAF7F2] dark:bg-[#202025] border border-[#DDD6CB] dark:border-[#27272A] relative">
                    <img src="{{ $bronze->cover_url ?: asset('images/default-cover.webp') }}" alt="{{ $bronze->title }}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80';" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-2 right-2 px-2 py-0.5 rounded-lg bg-black/80 backdrop-blur text-white text-[10px] font-mono font-bold">
                        {{ $bronze->console->short_name ?: $bronze->console->name }}
                    </div>
                </div>

                <div>
                    <h3 class="text-base font-black text-[#18181B] dark:text-white group-hover:text-[#CE2D2D] dark:group-hover:text-[#EF4444] transition-colors truncate">
                        {{ $bronze->title }}
                    </h3>
                    <div class="flex items-center justify-between text-xs font-mono text-gray-500 dark:text-gray-400 mt-1">
                        <span class="text-amber-500 font-bold">★ {{ number_format($bronze->rating_average ?: 5.0, 1) }}</span>
                        <span class="font-bold text-[#18181B] dark:text-white">{{ number_format($bronze->download_count) }} descargas</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('game.show', $bronze->slug) }}" class="mt-4 w-full py-2.5 rounded-xl bg-[#18181B] dark:bg-[#272730] hover:bg-[#CE2D2D] dark:hover:bg-[#CE2D2D] text-white text-xs font-mono font-bold text-center transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                <span>Ver Ficha & ROM</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
        @endif

    </section>
    @endif

    <!-- Full 25 Leaderboard Table / Cards -->
    <section class="bg-white dark:bg-[#18181B] border-2 border-[#1E1E1E] dark:border-[#27272A] rounded-3xl p-5 sm:p-7 space-y-4 shadow-sm">
        <div class="flex items-center justify-between border-b border-[#E5E0D8] dark:border-[#27272A] pb-3">
            <h2 class="text-sm font-mono font-bold uppercase text-[#18181B] dark:text-white tracking-wider flex items-center gap-2">
                <i data-lucide="list-ordered" class="w-4 h-4 text-[#CE2D2D]"></i>
                Tabla General de Posiciones (1 al {{ $topGames->count() }})
            </h2>
            <span class="text-[11px] font-mono text-gray-500 dark:text-gray-400">Actualizado en Tiempo Real</span>
        </div>

        <div class="divide-y divide-[#E5E0D8] dark:divide-[#27272A]">
            @forelse($topGames as $rank => $game)
            <div class="py-3.5 flex items-center justify-between gap-4 hover:bg-[#FAF7F2] dark:hover:bg-[#222228] px-3 rounded-2xl transition-colors group">
                
                <!-- Left: Rank Number + Cover Thumb + Title & Console -->
                <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                    
                    <!-- Rank Badge -->
                    <div class="w-8 sm:w-10 text-center font-mono font-black text-sm sm:text-base shrink-0 {{ $rank === 0 ? 'text-amber-500 text-lg' : ($rank === 1 ? 'text-slate-400 text-base' : ($rank === 2 ? 'text-amber-600 text-base' : 'text-gray-400 dark:text-gray-500 group-hover:text-[#CE2D2D] dark:group-hover:text-[#EF4444]')) }}">
                        #{{ str_pad($rank + 1, 2, '0', STR_PAD_LEFT) }}
                    </div>

                    <!-- Cover Thumb -->
                    <img src="{{ $game->cover_thumb_url ?: $game->cover_url }}" 
                         alt="{{ $game->title }}" 
                         onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=600&q=80';"
                         class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl object-cover bg-[#FAF7F2] dark:bg-[#202025] border border-[#DDD6CB] dark:border-[#27272A] shrink-0 aspect-square">

                    <!-- Title & Info -->
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('game.show', $game->slug) }}" class="font-black text-xs sm:text-sm text-[#18181B] dark:text-white group-hover:text-[#CE2D2D] dark:group-hover:text-[#EF4444] transition-colors truncate">
                                {{ $game->title }}
                            </a>
                            <span class="px-2 py-0.5 rounded-md bg-[#FAF7F2] dark:bg-[#272730] border border-[#DDD6CB] dark:border-[#3F3F46] text-[10px] font-mono font-bold text-gray-700 dark:text-gray-200">
                                {{ $game->console->short_name ?: $game->console->name }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 text-[11px] font-mono text-gray-500 dark:text-gray-400 mt-0.5">
                            <span class="text-amber-500 font-bold">★ {{ number_format($game->rating_average ?: 5.0, 1) }}</span>
                            <span>•</span>
                            <span class="text-gray-600 dark:text-gray-300 font-medium">{{ $game->formatted_size }}</span>
                            <span class="hidden sm:inline">•</span>
                            <span class="hidden sm:inline text-gray-600 dark:text-gray-300 font-bold">{{ number_format($game->download_count) }} descargas</span>
                        </div>
                    </div>

                </div>

                <!-- Right: Download Action Button -->
                <div class="shrink-0 flex items-center gap-2">
                    <a href="{{ route('game.show', $game->slug) }}" 
                       class="px-3.5 py-1.5 rounded-xl bg-[#18181B] dark:bg-[#CE2D2D] hover:bg-[#CE2D2D] dark:hover:bg-[#B71C1C] text-white text-xs font-mono font-bold transition-all shadow-sm flex items-center gap-1.5 active:scale-95">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">Descargar</span>
                    </a>
                </div>

            </div>
            @empty
            <div class="py-12 text-center space-y-2">
                <i data-lucide="trophy" class="w-8 h-8 text-gray-400 dark:text-gray-600 mx-auto"></i>
                <p class="text-xs font-mono text-gray-600 dark:text-gray-400 font-bold">No hay títulos registrados para esta categoría todavía.</p>
            </div>
            @endforelse
        </div>
    </section>

</main>
@endsection

