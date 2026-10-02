@extends('layouts.admin')

@section('title', 'Peticiones de Juegos — Administración ROMHUB')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-mono text-gray-500">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-300">Dashboard</a>
                <span>/</span>
                <span class="text-gray-300">Comunidad</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight font-sans">Peticiones de la Comunidad</h1>
            <p class="text-xs text-gray-400 font-sans mt-0.5">Prioriza la subida de ROMs según el interés y votos de los jugadores.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('requests.index') }}" target="_blank" class="px-4 py-2 rounded-xl bg-[#11141A] hover:bg-[#171B22] border border-[#232936] text-gray-300 text-xs font-semibold flex items-center gap-2 transition-colors">
                <i data-lucide="external-link" class="w-4 h-4 text-blue-400"></i> Ver en Portal Web
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 font-mono text-xs">
        <a href="{{ route('admin.requests.index') }}" class="p-4 rounded-xl bg-[#11141A] border border-[#232936] hover:border-gray-500 transition-all">
            <div class="text-gray-400 text-[10px] uppercase tracking-wider">Total</div>
            <div class="text-xl font-bold text-white mt-1">{{ number_format($stats['total']) }}</div>
        </a>
        <a href="{{ route('admin.requests.index', ['status' => 'pending']) }}" class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 hover:border-amber-500/40 transition-all">
            <div class="text-amber-400 text-[10px] uppercase tracking-wider">Esperando</div>
            <div class="text-xl font-bold text-amber-300 mt-1">{{ number_format($stats['pending']) }}</div>
        </a>
        <a href="{{ route('admin.requests.index', ['status' => 'in_progress']) }}" class="p-4 rounded-xl bg-blue-500/10 border border-blue-500/20 hover:border-blue-500/40 transition-all">
            <div class="text-blue-400 text-[10px] uppercase tracking-wider">En Proceso</div>
            <div class="text-xl font-bold text-blue-300 mt-1">{{ number_format($stats['in_progress']) }}</div>
        </a>
        <a href="{{ route('admin.requests.index', ['status' => 'completed']) }}" class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 hover:border-emerald-500/40 transition-all">
            <div class="text-emerald-400 text-[10px] uppercase tracking-wider">Subidos</div>
            <div class="text-xl font-bold text-emerald-300 mt-1">{{ number_format($stats['completed']) }}</div>
        </a>
        <a href="{{ route('admin.requests.index', ['status' => 'rejected']) }}" class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 hover:border-red-500/40 transition-all">
            <div class="text-red-400 text-[10px] uppercase tracking-wider">Rechazados</div>
            <div class="text-xl font-bold text-red-300 mt-1">{{ number_format($stats['rejected']) }}</div>
        </a>
    </div>

    <!-- Filters Bar -->
    <div class="bg-[#11141A] border border-[#232936] rounded-2xl p-4 shadow-xl">
        <form action="{{ route('admin.requests.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3 font-mono text-xs">
            
            <!-- Search -->
            <div class="relative flex-1 w-full">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                <input type="text" 
                       name="q" 
                       value="{{ request('q') }}" 
                       placeholder="Buscar por juego, usuario o nota..." 
                       class="w-full bg-[#0A0C0F] border border-[#232936] rounded-xl pl-9 pr-3 py-2 text-white placeholder-gray-500 focus:border-blue-500">
            </div>

            <!-- Console Filter -->
            <select name="console_id" onchange="this.form.submit()" class="w-full sm:w-auto bg-[#0A0C0F] border border-[#232936] rounded-xl px-3 py-2 text-gray-300">
                <option value="">Todas las Consolas</option>
                @foreach($consoles as $c)
                <option value="{{ $c->id }}" {{ request('console_id') == $c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                </option>
                @endforeach
            </select>

            <!-- Status Filter -->
            <select name="status" onchange="this.form.submit()" class="w-full sm:w-auto bg-[#0A0C0F] border border-[#232936] rounded-xl px-3 py-2 text-gray-300">
                <option value="">Todos los Estados</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Esperando Votos</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En Proceso</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completados</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rechazados</option>
            </select>

            <!-- Sorting -->
            <select name="sort" onchange="this.form.submit()" class="w-full sm:w-auto bg-[#0A0C0F] border border-[#232936] rounded-xl px-3 py-2 text-gray-300">
                <option value="votes" {{ request('sort') === 'votes' ? 'selected' : '' }}>Más Votados</option>
                <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Más Recientes</option>
                <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Más Antiguos</option>
            </select>

            @if(request()->anyFilled(['q', 'console_id', 'status', 'sort']))
            <a href="{{ route('admin.requests.index') }}" class="p-2 rounded-xl bg-gray-800 text-gray-400 hover:text-white" title="Limpiar filtros">
                <i data-lucide="x" class="w-4 h-4"></i>
            </a>
            @endif
        </form>
    </div>

    <!-- Table of Requests -->
    <div class="bg-[#11141A] border border-[#232936] rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sans">
                <thead class="bg-[#0A0C0F] text-gray-400 font-mono text-[11px] uppercase tracking-wider border-b border-[#232936]">
                    <tr>
                        <th class="py-3.5 px-4 w-20 text-center">Votos</th>
                        <th class="py-3.5 px-4">Videojuego Solicitado</th>
                        <th class="py-3.5 px-4">Consola & Región</th>
                        <th class="py-3.5 px-4">Solicitante</th>
                        <th class="py-3.5 px-4">Estado</th>
                        <th class="py-3.5 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#232936]">
                    @forelse($requests as $req)
                    @php $meta = $req->getStatusMeta(); @endphp
                    <tr class="hover:bg-[#141820] transition-colors">
                        
                        <!-- Votes count -->
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-red-950/40 text-red-400 border border-red-500/30 font-mono font-black text-sm">
                                <i data-lucide="flame" class="w-3.5 h-3.5 text-red-400"></i>
                                {{ $req->votes_count }}
                            </span>
                        </td>

                        <!-- Title & Notes -->
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-white text-sm">{{ $req->title }}</div>
                            @if($req->notes)
                            <div class="text-[11px] text-gray-400 font-sans mt-0.5 line-clamp-2">
                                <span class="text-gray-500 font-mono">Nota:</span> {{ $req->notes }}
                            </div>
                            @endif
                        </td>

                        <!-- Console & Region -->
                        <td class="py-3.5 px-4 font-mono">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold" 
                                  style="background-color: {{ $req->console->color ? $req->console->color . '20' : '#CE2D2D20' }}; color: {{ $req->console->color ?: '#CE2D2D' }}; border: 1px solid {{ $req->console->color ? $req->console->color . '40' : '#CE2D2D40' }}">
                                {{ $req->console->name }}
                            </span>
                            @if($req->region)
                            <div class="text-[10px] text-gray-500 mt-1 font-sans">{{ $req->region }}</div>
                            @endif
                        </td>

                        <!-- Requester -->
                        <td class="py-3.5 px-4 text-gray-400 font-mono text-[11px]">
                            <div class="text-white">{{ $req->requester_name ?: 'Anónimo' }}</div>
                            <div class="text-gray-500 text-[10px]">{{ $req->created_at->format('d/m/Y H:i') }}</div>
                        </td>

                        <!-- Status Badge + Form -->
                        <td class="py-3.5 px-4">
                            <form action="{{ route('admin.requests.updateStatus', $req->id) }}" method="POST" class="flex items-center gap-1.5">
                                @csrf
                                @method('PATCH')
                                <select name="status" 
                                        onchange="this.form.submit()" 
                                        class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-[#0A0C0F] border border-[#232936] text-white focus:border-blue-500">
                                    <option value="pending" {{ $req->status === 'pending' ? 'selected' : '' }}>⏳ Esperando</option>
                                    <option value="in_progress" {{ $req->status === 'in_progress' ? 'selected' : '' }}>⚙️ En Proceso</option>
                                    <option value="completed" {{ $req->status === 'completed' ? 'selected' : '' }}>✅ Subido</option>
                                    <option value="rejected" {{ $req->status === 'rejected' ? 'selected' : '' }}>❌ Rechazado</option>
                                </select>
                            </form>
                            @if($req->status === 'completed' && $req->completedGame)
                            <a href="{{ route('game.show', $req->completedGame->slug) }}" target="_blank" class="text-[10px] text-emerald-400 hover:underline flex items-center gap-1 mt-1 font-mono">
                                <i data-lucide="link" class="w-3 h-3"></i> {{ $req->completedGame->title }}
                            </a>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5 font-mono">
                                
                                <!-- Link to Scraper or Game create with title prefilled -->
                                <a href="{{ route('admin.games.create', ['title' => $req->title, 'console_id' => $req->console_id]) }}" 
                                   class="p-1.5 rounded-lg bg-emerald-600/20 text-emerald-300 hover:bg-emerald-600 hover:text-white border border-emerald-500/30 transition-colors"
                                   title="Subir esta ROM al Vault">
                                    <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                </a>

                                <form action="{{ route('admin.requests.destroy', $req->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar esta petición?')">
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
                        <td colspan="6" class="py-12 text-center text-gray-500 font-mono text-xs">
                            No hay peticiones registradas con los filtros actuales.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-[#232936]">
            {{ $requests->links() }}
        </div>
    </div>

</div>
@endsection
