@extends('layouts.admin')

@section('title', 'Catálogo de Videojuegos — Administración ROMHUB')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight font-sans">Catálogo de Videojuegos</h1>
            <p class="text-xs text-gray-400 font-mono mt-0.5">Gestión de ROMs, metadatos, hashes criptográficos y archivos en Cloudflare R2</p>
        </div>
        <a href="{{ route('admin.games.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs uppercase tracking-wide flex items-center gap-2 transition-all shadow-lg shadow-blue-600/30">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Nuevo Videojuego
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <form action="{{ route('admin.games.index') }}" method="GET" class="bg-[#11141A] border border-[#232936] rounded-xl p-4 flex flex-wrap items-center justify-between gap-3 text-xs font-mono">
        <div class="flex flex-wrap items-center gap-3 flex-1">
            <div class="relative min-w-[240px] flex-1">
                <i data-lucide="search" class="w-4 h-4 text-gray-500 absolute left-3 top-2.5 pointer-events-none"></i>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Buscar por título, slug o serial..." 
                    class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg pl-9 pr-3 py-2 text-xs text-gray-200 placeholder-gray-500 focus:outline-none focus:border-blue-500 font-sans"
                >
            </div>

            <select name="console_id" onchange="this.form.submit()" class="bg-[#0A0C0F] border border-[#232936] rounded-lg px-3 py-2 text-gray-300 focus:outline-none focus:border-blue-500">
                <option value="">Todas las Consolas</option>
                @foreach($consoles as $con)
                <option value="{{ $con->id }}" {{ request('console_id') == $con->id ? 'selected' : '' }}>{{ $con->name }}</option>
                @endforeach
            </select>

            <select name="status" onchange="this.form.submit()" class="bg-[#0A0C0F] border border-[#232936] rounded-lg px-3 py-2 text-gray-300 focus:outline-none focus:border-blue-500">
                <option value="">Todos los Estados</option>
                <option value="PUBLISHED" {{ request('status') === 'PUBLISHED' ? 'selected' : '' }}>Publicado</option>
                <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>Borrador</option>
                <option value="ARCHIVED" {{ request('status') === 'ARCHIVED' ? 'selected' : '' }}>Archivado</option>
            </select>
        </div>

        @if(request()->hasAny(['search', 'console_id', 'status']))
        <a href="{{ route('admin.games.index') }}" class="text-rose-400 hover:text-rose-300 text-xs">
            &times; Limpiar
        </a>
        @endif
    </form>

    <!-- Data Table -->
    <div class="bg-[#11141A] border border-[#232936] rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sans">
                <thead class="bg-[#0A0C0F] border-b border-[#232936] text-[10px] font-mono uppercase text-gray-400">
                    <tr>
                        <th class="py-3.5 px-4">Carátula & Título</th>
                        <th class="py-3.5 px-3">Consola</th>
                        <th class="py-3.5 px-3">Región / Año</th>
                        <th class="py-3.5 px-3">Tamaño / Hashes</th>
                        <th class="py-3.5 px-3">Badges</th>
                        <th class="py-3.5 px-3">Estado</th>
                        <th class="py-3.5 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#232936]/60">
                    @forelse($games as $game)
                    <tr class="hover:bg-[#171B22]/50 transition-colors">
                        
                        <!-- Title & Cover -->
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $game->cover_thumb_url ?: $game->cover_url }}" 
                                     alt="" 
                                     class="w-10 h-13 object-cover rounded bg-gray-900 border border-[#232936] shrink-0">
                                <div class="min-w-0 max-w-xs">
                                    <a href="{{ route('game.show', $game->slug) }}" target="_blank" class="font-bold text-white hover:text-blue-400 transition-colors truncate block">
                                        {{ $game->title }}
                                    </a>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[10px] font-mono text-gray-500 truncate block">{{ $game->serial ?: 'No Serial' }}</span>
                                        @if($game->is_spotlight)
                                            <span class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[9px] font-mono font-bold">★ HERO</span>
                                        @endif
                                        @if($game->is_featured)
                                            <span class="px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-300 border border-blue-500/30 text-[9px] font-mono font-medium">RIEL</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Console -->
                        <td class="py-3 px-3 font-mono">
                            <span class="px-2 py-0.5 rounded bg-blue-600/20 text-blue-400 border border-blue-500/30 text-[10px] font-bold">
                                {{ $game->console->name }}
                            </span>
                        </td>

                        <!-- Region / Year -->
                        <td class="py-3 px-3 font-mono text-gray-300">
                            <div>{{ $game->region ?: 'Global' }}</div>
                            <span class="text-[10px] text-gray-500">{{ $game->release_year ?: 'N/A' }}</span>
                        </td>

                        <!-- Size & Integrity -->
                        <td class="py-3 px-3 font-mono text-gray-300">
                            <div class="font-bold text-emerald-400">{{ $game->formatted_size }}</div>
                            <span class="text-[10px] text-gray-500">CRC: {{ $game->crc32 ?: 'OK' }}</span>
                        </td>

                        <!-- Badges -->
                        <td class="py-3 px-3">
                            <div class="flex flex-wrap gap-1 max-w-[150px]">
                                @foreach($game->badges as $badge)
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-semibold" style="background-color: {{ $badge->bg_color }}; color: {{ $badge->text_color }}; border: 1px solid {{ $badge->border_color }}">
                                    {{ $badge->name }}
                                </span>
                                @endforeach
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="py-3 px-3 font-mono">
                            @if($game->status === 'PUBLISHED')
                                <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] font-bold">PUBLICADO</span>
                            @elseif($game->status === 'DRAFT')
                                <span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-400 border border-amber-500/30 text-[10px] font-bold">BORRADOR</span>
                            @else
                                <span class="px-2 py-0.5 rounded bg-gray-500/20 text-gray-400 border border-gray-500/30 text-[10px] font-bold">ARCHIVADO</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5 font-mono">
                                
                                <a href="{{ route('game.show', $game->slug) }}" target="_blank" class="p-1.5 rounded-lg bg-[#0A0C0F] hover:bg-[#171B22] text-gray-400 hover:text-white border border-[#232936] transition-colors" title="Ver en Web">
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                </a>

                                <a href="{{ route('admin.games.edit', $game->id) }}" class="p-1.5 rounded-lg bg-blue-600/20 hover:bg-blue-600 text-blue-300 hover:text-white border border-blue-500/30 transition-colors" title="Editar Metadatos">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </a>

                                <form action="{{ route('admin.games.duplicate', $game->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1.5 rounded-lg bg-[#0A0C0F] hover:bg-[#171B22] text-gray-400 hover:text-emerald-400 border border-[#232936] transition-colors" title="Duplicar">
                                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>

                                <form action="{{ route('admin.games.destroy', $game->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este videojuego?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-600 text-rose-400 hover:text-white border border-rose-500/20 transition-colors" title="Eliminar">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>

                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-gray-500 font-mono text-xs">
                            No se encontraron títulos en el catálogo con los filtros actuales.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-[#232936]">
            {{ $games->links() }}
        </div>
    </div>

</div>
@endsection
