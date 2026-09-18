@extends('layouts.web')

@section('title', 'Mi Biblioteca & Perfil de Usuario — ' . \App\Models\Setting::get('site_name', 'ROMHUB'))

@section('content')
<main class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6 py-8 space-y-8">

    <!-- Profile Header Card -->
    <div class="bg-white border-2 border-[#1E1E1E] rounded-3xl p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-sm">
        
        <div class="flex items-center gap-5">
            <!-- User Avatar with Retro Badge -->
            <div class="w-20 h-20 rounded-2xl bg-[#CE2D2D] border-2 border-[#1E1E1E] flex items-center justify-center text-white font-extrabold text-2xl font-mono shadow-md">
                {{ substr($user->name, 0, 2) }}
            </div>

            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-black text-[#18181B] font-sans">{{ $user->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-[#FDF2F2] text-[#CE2D2D] border border-[#FCA5A5] text-xs font-mono font-bold">
                        Nivel {{ $user->level }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 font-mono">&#64;{{ $user->username }} • Miembro desde {{ $user->created_at->format('M Y') }}</p>
                
                <!-- XP Bar -->
                <div class="pt-2 max-w-xs space-y-1 font-mono text-[10px]">
                    <div class="flex justify-between text-gray-600 font-bold">
                        <span>XP: {{ $user->xp }} / {{ $user->level * 500 }}</span>
                        <span class="text-[#CE2D2D]">{{ round(($user->xp / ($user->level * 500)) * 100) }}%</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-[#FAF7F2] border border-[#DDD6CB] overflow-hidden">
                        <div class="h-full bg-[#CE2D2D] rounded-full" style="width: {{ min(100, ($user->xp / ($user->level * 500)) * 100) }}%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logout / Session Actions -->
        <div class="flex items-center gap-3 font-sans text-xs">
            @if($user->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-amber-50 text-amber-800 border border-amber-300 hover:bg-amber-100 font-bold flex items-center gap-2 transition-colors shadow-sm">
                <i data-lucide="shield" class="w-4 h-4"></i> Panel de Administración
            </a>
            @endif

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#FAF7F2] hover:bg-[#CE2D2D] hover:text-white text-gray-700 border border-[#1E1E1E] font-bold transition-colors flex items-center gap-2 shadow-sm cursor-pointer">
                    <i data-lucide="log-out" class="w-4 h-4"></i> Cerrar Sesión
                </button>
            </form>
        </div>

    </div>

    <!-- Favorites Vault Section -->
    <section class="space-y-4">
        <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="bookmark" class="w-5 h-5 text-[#CE2D2D]"></i>
                <h2 class="text-lg font-black text-[#18181B] font-sans">Mi Bóveda de Favoritos</h2>
            </div>
            <span class="text-xs font-mono text-gray-500 font-bold">{{ $favorites->total() }} títulos guardados</span>
        </div>

        @if($favorites->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($favorites as $game)
            <div class="bg-white border-2 border-[#1E1E1E] rounded-2xl overflow-hidden flex flex-col justify-between transition-all duration-200 group hover:-translate-y-1 hover:shadow-lg relative">
                
                <div class="aspect-[3/4.1] bg-[#FAF7F2] relative overflow-hidden border-b border-[#E5E0D8]">
                    <a href="{{ route('game.show', $game->slug) }}" class="block w-full h-full">
                        <img src="{{ $game->cover_thumb_url ?: $game->cover_url }}" 
                             alt="{{ $game->title }}" 
                             loading="lazy"
                             class="w-full h-full object-cover object-center">
                    </a>
                    
                    <div class="absolute top-2 left-2 right-2 flex items-center justify-between z-10">
                        <span class="console-card-badge px-2 py-0.5 rounded bg-white/95 dark:bg-[#18181B]/95 text-[#18181B] dark:text-white border border-[#1E1E1E] dark:border-[#3F3F46] text-[10px] font-mono font-black backdrop-blur">
                            {{ $game->console->name }}
                        </span>
                        
                        <button 
                            @click="fetch('{{ route('favorites.toggle', $game->slug) }}', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                            }).then(r => r.json()).then(d => {
                                window.location.reload();
                            })"
                            class="w-6 h-6 rounded-md bg-white border border-[#1E1E1E] text-rose-600 flex items-center justify-center transition-colors cursor-pointer" title="Quitar de favoritos">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>

                <div class="p-3 space-y-2 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="card-game-title text-[13px] sm:text-sm font-black text-[#18181B] group-hover:text-[#CE2D2D] transition-colors line-clamp-1 leading-snug font-sans tracking-tight">
                            <a href="{{ route('game.show', $game->slug) }}">{{ $game->title }}</a>
                        </h3>
                    </div>

                    <div class="pt-2 border-t border-[#E5E0D8] flex items-center justify-between text-[11px] font-mono">
                        <span class="text-[10px] text-gray-500 font-bold">{{ $game->formatted_size }}</span>
                        <span class="text-amber-500 font-bold flex items-center gap-0.5">★ {{ number_format($game->rating_average ?: 5.0, 1) }}</span>
                    </div>
                </div>

            </div>
            @endforeach
        </div>

        <div class="pt-4">
            {{ $favorites->links() }}
        </div>
        @else
        <div class="p-12 text-center bg-white border-2 border-dashed border-[#1E1E1E] rounded-2xl space-y-3">
            <i data-lucide="bookmark-x" class="w-8 h-8 text-gray-400 mx-auto"></i>
            <h3 class="text-sm font-bold text-[#18181B] font-sans">Aún no tienes títulos en favoritos</h3>
            <p class="text-xs text-gray-600 font-mono">Explora el catálogo y pulsa en el corazón para guardarlos aquí.</p>
        </div>
        @endif
    </section>

</main>
@endsection
