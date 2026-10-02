@extends('layouts.web')

@section('title', 'Peticiones de Juegos y ROMs de la Comunidad — ' . \App\Models\Setting::get('site_name', 'ROM Preservation Vault'))
@section('meta_description', '¿Buscas una ROM o juego retro que aún no está en nuestra bóveda? Solicítalo aquí o vota por las peticiones de otros jugadores.')

@section('content')
<div x-data="gameRequestsComponent()" class="space-y-8 py-4">

    <!-- Hero Header -->
    <section class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6">
        <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-br from-[#18181B] via-[#1E1E24] to-[#121214] border border-[#27272A] p-6 sm:p-10 shadow-xl text-white">
            <!-- Background Ambient Glow -->
            <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-[#CE2D2D]/15 blur-3xl pointer-events-none"></div>
            <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-blue-600/10 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="space-y-3 text-center md:text-left max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#CE2D2D]/20 border border-[#CE2D2D]/40 text-[#FCA5A5] text-xs font-mono font-bold uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-[#CE2D2D] animate-ping"></span>
                        Comunidad Retro & Preservación
                    </div>
                    <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black tracking-tight font-sans text-white">
                        Peticiones de Juegos
                    </h1>
                    <p class="text-sm sm:text-base text-gray-300 font-sans leading-relaxed">
                        ¿Buscas una ROM que aún no encuentras en nuestro archivo? <strong>Pídela aquí</strong> o vota por las solicitudes de otros jugadores. Subimos con máxima prioridad los títulos más apoyados.
                    </p>
                </div>

                <!-- Primary Action: Open Request Modal -->
                <div class="shrink-0 flex flex-col sm:flex-row gap-3">
                    <button type="button" 
                            @click="openModal = true"
                            class="px-6 sm:px-8 py-3.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-black text-xs uppercase tracking-wider transition-all transform hover:scale-105 active:scale-95 shadow-lg shadow-red-600/30 flex items-center justify-center gap-2.5 cursor-pointer">
                        <i data-lucide="plus-circle" class="w-4 h-4 stroke-[2.5]"></i>
                        <span>Solicitar un Juego</span>
                    </button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="relative z-10 grid grid-cols-2 sm:grid-cols-4 gap-3 pt-6 mt-6 border-t border-white/10 text-center font-mono">
                <div class="p-2 rounded-xl bg-white/5 border border-white/5">
                    <div class="text-xl sm:text-2xl font-black text-white">{{ number_format($stats['total']) }}</div>
                    <div class="text-[11px] text-gray-400 uppercase tracking-wider mt-0.5">Total Solicitudes</div>
                </div>
                <div class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20">
                    <div class="text-xl sm:text-2xl font-black text-amber-400">{{ number_format($stats['pending']) }}</div>
                    <div class="text-[11px] text-amber-300/80 uppercase tracking-wider mt-0.5">Esperando Votos</div>
                </div>
                <div class="p-2 rounded-xl bg-blue-500/10 border border-blue-500/20">
                    <div class="text-xl sm:text-2xl font-black text-blue-400">{{ number_format($stats['in_progress']) }}</div>
                    <div class="text-[11px] text-blue-300/80 uppercase tracking-wider mt-0.5">En Búsqueda / Subida</div>
                </div>
                <div class="p-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                    <div class="text-xl sm:text-2xl font-black text-emerald-400">{{ number_format($stats['completed']) }}</div>
                    <div class="text-[11px] text-emerald-300/80 uppercase tracking-wider mt-0.5">¡Subidos al Vault!</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filters & Nav Controls -->
    <section class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6">
        
        <!-- Flash Notifications -->
        @if(session('success'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-mono flex items-center gap-3 shadow-sm">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if(session('info'))
        <div class="mb-4 p-4 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300 text-xs font-mono flex items-center gap-3 shadow-sm">
            <i data-lucide="info" class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0"></i>
            <span>{{ session('info') }}</span>
        </div>
        @endif

        <div class="p-3 sm:p-4 rounded-2xl bg-white dark:bg-[#18181B] border border-[#DDD6CB] dark:border-[#27272A] shadow-sm flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
            
            <!-- Tab Filters -->
            <div class="flex flex-wrap items-center gap-1.5 font-mono text-xs">
                <a href="{{ route('requests.index', array_merge(request()->except('tab', 'page'), ['tab' => 'mas-votados'])) }}"
                   class="px-3.5 py-2 rounded-xl font-bold transition-all flex items-center gap-1.5 {{ $tab === 'mas-votados' ? 'bg-[#CE2D2D] text-white shadow-sm' : 'bg-gray-100 dark:bg-[#27272A] text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-[#323238]' }}">
                    <i data-lucide="flame" class="w-3.5 h-3.5"></i>
                    <span>Más Votados</span>
                </a>
                <a href="{{ route('requests.index', array_merge(request()->except('tab', 'page'), ['tab' => 'recientes'])) }}"
                   class="px-3.5 py-2 rounded-xl font-bold transition-all flex items-center gap-1.5 {{ $tab === 'recientes' ? 'bg-[#CE2D2D] text-white shadow-sm' : 'bg-gray-100 dark:bg-[#27272A] text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-[#323238]' }}">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Recientes</span>
                </a>
                <a href="{{ route('requests.index', array_merge(request()->except('tab', 'page'), ['tab' => 'en-proceso'])) }}"
                   class="px-3.5 py-2 rounded-xl font-bold transition-all flex items-center gap-1.5 {{ $tab === 'en-proceso' ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-[#27272A] text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-[#323238]' }}">
                    <i data-lucide="loader" class="w-3.5 h-3.5"></i>
                    <span>En Proceso</span>
                </a>
                <a href="{{ route('requests.index', array_merge(request()->except('tab', 'page'), ['tab' => 'completados'])) }}"
                   class="px-3.5 py-2 rounded-xl font-bold transition-all flex items-center gap-1.5 {{ $tab === 'completados' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 dark:bg-[#27272A] text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-[#323238]' }}">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                    <span>Ya Subidos</span>
                </a>
            </div>

            <!-- Search & Console Filter Form -->
            <form action="{{ route('requests.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">

                <!-- Console selector -->
                <select name="console" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-2 rounded-xl bg-gray-100 dark:bg-[#27272A] border border-[#DDD6CB] dark:border-[#3F3F46] text-xs font-mono text-[#18181B] dark:text-white focus:outline-none focus:border-[#CE2D2D]">
                    <option value="">Todas las Consolas</option>
                    @foreach($consoles as $c)
                    <option value="{{ $c->slug }}" {{ request('console') === $c->slug ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                    @endforeach
                </select>

                <!-- Search input -->
                <div class="relative w-full sm:w-56">
                    <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           name="q" 
                           value="{{ request('q') }}" 
                           placeholder="Buscar petición..." 
                           class="w-full pl-8 pr-3 py-2 rounded-xl bg-gray-100 dark:bg-[#27272A] border border-[#DDD6CB] dark:border-[#3F3F46] text-xs font-mono text-[#18181B] dark:text-white placeholder-gray-400 focus:outline-none focus:border-[#CE2D2D]">
                </div>
            </form>
        </div>
    </section>

    <!-- Requests Listing Grid -->
    <section class="{{ \App\Models\Setting::get('container_max_width', 'max-w-[1200px]') }} mx-auto px-4 lg:px-6">
        @if($requests->count() > 0)
        <div class="space-y-3.5">
            @foreach($requests as $req)
            @php
                $meta = $req->getStatusMeta();
                $hasVoted = in_array($req->id, $myVotedIds);
            @endphp
            <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#18181B] border border-[#DDD6CB] dark:border-[#27272A] hover:border-gray-400 dark:hover:border-[#3F3F46] shadow-sm transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                
                <!-- Left: Upvote Control + Game Info -->
                <div class="flex items-start sm:items-center gap-4 w-full sm:w-auto">
                    
                    <!-- Upvote Button -->
                    <button type="button" 
                            @click="toggleVote({{ $req->id }}, $event)"
                            class="group shrink-0 flex flex-col items-center justify-center w-14 h-14 sm:w-16 sm:h-16 rounded-2xl border transition-all transform active:scale-95 cursor-pointer font-mono"
                            :class="votedMap[{{ $req->id }}] ? 'bg-[#CE2D2D] border-[#CE2D2D] text-white shadow-md shadow-red-500/20' : 'bg-gray-50 dark:bg-[#202024] hover:bg-gray-100 dark:hover:bg-[#27272A] border-[#DDD6CB] dark:border-[#323238] text-gray-700 dark:text-gray-300'"
                            title="Apoyar esta petición">
                        <i data-lucide="chevron-up" 
                           class="w-5 h-5 transition-transform group-hover:-translate-y-0.5"
                           :class="votedMap[{{ $req->id }}] ? 'stroke-[3] text-white' : 'stroke-[2] text-gray-400 dark:text-gray-500'"></i>
                        <span class="text-xs sm:text-sm font-black leading-none mt-0.5" 
                              x-text="votesMap[{{ $req->id }}] ?? {{ $req->votes_count }}">
                            {{ $req->votes_count }}
                        </span>
                    </button>

                    <!-- Title & Badges -->
                    <div class="space-y-1.5 flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Console Badge -->
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase shadow-xs" 
                                  style="background-color: {{ $req->console->color ? $req->console->color . '20' : '#CE2D2D20' }}; color: {{ $req->console->color ?: '#CE2D2D' }}; border: 1px solid {{ $req->console->color ? $req->console->color . '40' : '#CE2D2D40' }}">
                                {{ $req->console->name }}
                            </span>

                            @if($req->region)
                            <span class="px-2 py-0.5 rounded-full bg-gray-100 dark:bg-[#27272A] text-gray-600 dark:text-gray-300 text-[10px] font-mono font-semibold border border-gray-200 dark:border-[#3F3F46]">
                                {{ $req->region }}
                            </span>
                            @endif

                            <!-- Status Badge -->
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold flex items-center gap-1 shadow-xs"
                                  style="background-color: {{ $meta['bg'] }}; color: {{ $meta['color'] }}; border: 1px solid {{ $meta['border'] }}">
                                <i data-lucide="{{ $meta['icon'] }}" class="w-3 h-3 {{ $req->status === 'in_progress' ? 'animate-spin' : '' }}"></i>
                                <span>{{ $meta['label'] }}</span>
                            </span>
                        </div>

                        <!-- Game Requested Title -->
                        <h2 class="text-base sm:text-lg font-bold text-[#18181B] dark:text-white tracking-tight font-sans">
                            {{ $req->title }}
                        </h2>

                        @if($req->notes)
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-sans line-clamp-2">
                            <span class="font-mono text-gray-400 dark:text-gray-500">Nota:</span> {{ $req->notes }}
                        </p>
                        @endif

                        <div class="text-[11px] font-mono text-gray-400 dark:text-gray-500 flex items-center gap-2 pt-0.5">
                            <span>Solicitado por <strong class="text-gray-600 dark:text-gray-300 font-sans">{{ $req->requester_name ?: 'Coleccionista Retro' }}</strong></span>
                            <span>•</span>
                            <span>{{ $req->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Action / Direct Link if Completed -->
                <div class="shrink-0 w-full sm:w-auto flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-100 dark:border-white/5">
                    @if($req->status === 'completed' && $req->completedGame)
                        <a href="{{ route('game.show', $req->completedGame->slug) }}" 
                           class="w-full sm:w-auto px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-mono font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition-all hover:scale-105">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>Descargar ROM Ahora</span>
                        </a>
                    @elseif($req->status === 'in_progress')
                        <span class="text-xs font-mono text-blue-600 dark:text-blue-400 flex items-center gap-1.5 font-bold">
                            <i data-lucide="clock" class="w-3.5 h-3.5 animate-pulse"></i>
                            Subida programada
                        </span>
                    @else
                        <span class="text-[11px] font-mono text-gray-400 dark:text-gray-500">
                            {{ $req->votes_count }} {{ $req->votes_count == 1 ? 'persona lo quiere' : 'personas lo quieren' }}
                        </span>
                    @endif
                </div>

            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pt-6">
            {{ $requests->links() }}
        </div>

        @else
        <!-- Empty State -->
        <div class="p-12 text-center rounded-2xl bg-white dark:bg-[#18181B] border border-[#DDD6CB] dark:border-[#27272A] space-y-4">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-[#CE2D2D]/10 text-[#CE2D2D] flex items-center justify-center">
                <i data-lucide="gamepad" class="w-7 h-7"></i>
            </div>
            <div class="space-y-1 max-w-md mx-auto">
                <h3 class="text-base font-bold text-[#18181B] dark:text-white font-sans">No se encontraron peticiones con estos filtros</h3>
                <p class="text-xs text-gray-500 font-sans">¿No está el juego que buscas? ¡Sé el primero en solicitarlo a la comunidad!</p>
            </div>
            <button type="button" 
                    @click="openModal = true"
                    class="px-5 py-2.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-mono font-bold text-xs transition-colors inline-flex items-center gap-2 shadow-sm">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Pedir este Juego Ahora</span>
            </button>
        </div>
        @endif

    </section>

    <!-- CREATE REQUEST MODAL -->
    <div x-show="openModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/75 backdrop-blur-xs transition-opacity" 
             @click="openModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-[#18181B] border border-[#DDD6CB] dark:border-[#27272A] p-6 sm:p-8 text-left shadow-2xl transition-all w-full max-w-lg space-y-6">
                
                <div class="flex items-center justify-between border-b border-[#DDD6CB] dark:border-[#27272A] pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#CE2D2D]/10 text-[#CE2D2D] flex items-center justify-center">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-[#18181B] dark:text-white font-sans">Solicitar una ROM al Vault</h3>
                            <p class="text-[11px] text-gray-500 font-mono">Los juegos más votados se suben con prioridad</p>
                        </div>
                    </div>
                    <button type="button" @click="openModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form action="{{ route('requests.store') }}" method="POST" class="space-y-4 font-mono text-xs">
                    @csrf

                    <!-- Title -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-gray-700 dark:text-gray-200">
                            Título del Videojuego *
                        </label>
                        <input type="text" 
                               name="title" 
                               required 
                               placeholder="ej: Silent Hill: Shattered Memories" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-gray-50 dark:bg-[#0A0C0F] border border-[#DDD6CB] dark:border-[#27272A] text-xs text-[#18181B] dark:text-white font-sans focus:outline-none focus:border-[#CE2D2D]">
                    </div>

                    <!-- Console -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-gray-700 dark:text-gray-200">
                            Consola / Plataforma *
                        </label>
                        <select name="console_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-gray-50 dark:bg-[#0A0C0F] border border-[#DDD6CB] dark:border-[#27272A] text-xs text-[#18181B] dark:text-white focus:outline-none focus:border-[#CE2D2D]">
                            <option value="">Selecciona una consola...</option>
                            @foreach($consoles as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Region -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-gray-700 dark:text-gray-200">
                            Región o Idioma Preferido (Opcional)
                        </label>
                        <input type="text" 
                               name="region" 
                               placeholder="ej: Español (Multi-5), USA, EUR" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-gray-50 dark:bg-[#0A0C0F] border border-[#DDD6CB] dark:border-[#27272A] text-xs text-[#18181B] dark:text-white font-sans focus:outline-none focus:border-[#CE2D2D]">
                    </div>

                    <!-- Notes -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-gray-700 dark:text-gray-200">
                            Detalles o Notas Adicionales (Opcional)
                        </label>
                        <textarea name="notes" 
                                  rows="3" 
                                  placeholder="ej: Con parche de traducción al español, versión rev 1.1, etc." 
                                  class="w-full px-3.5 py-2 rounded-xl bg-gray-50 dark:bg-[#0A0C0F] border border-[#DDD6CB] dark:border-[#27272A] text-xs text-[#18181B] dark:text-white font-sans focus:outline-none focus:border-[#CE2D2D]"></textarea>
                    </div>

                    @guest
                    <!-- Requester Nickname -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-gray-700 dark:text-gray-200">
                            Tu Apodo o Nombre (Opcional)
                        </label>
                        <input type="text" 
                               name="requester_name" 
                               placeholder="ej: RedumpCollector99" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-gray-50 dark:bg-[#0A0C0F] border border-[#DDD6CB] dark:border-[#27272A] text-xs text-[#18181B] dark:text-white font-sans focus:outline-none focus:border-[#CE2D2D]">
                    </div>
                    @endguest

                    <!-- Submit -->
                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#DDD6CB] dark:border-[#27272A]">
                        <button type="button" @click="openModal = false" class="px-4 py-2.5 rounded-xl text-gray-500 hover:text-gray-700 dark:hover:text-white text-xs font-bold font-mono">
                            Cancelar
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#CE2D2D] hover:bg-[#B71C1C] text-white font-mono font-bold text-xs uppercase tracking-wider shadow-md shadow-red-500/25 transition-all">
                            Publicar Petición
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>

<script>
function gameRequestsComponent() {
    return {
        openModal: false,
        votedMap: @json(array_fill_keys($myVotedIds, true)),
        votesMap: {},

        toggleVote(requestId, event) {
            const wasVoted = !!this.votedMap[requestId];
            const currentVotes = this.votesMap[requestId] !== undefined ? this.votesMap[requestId] : null;

            // Optimistic UI update
            this.votedMap[requestId] = !wasVoted;
            if (currentVotes !== null) {
                this.votesMap[requestId] = wasVoted ? Math.max(0, currentVotes - 1) : currentVotes + 1;
            }

            fetch('{{ url("peticiones") }}/' + requestId + '/vote', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    this.votedMap[requestId] = d.voted;
                    this.votesMap[requestId] = d.votes_count;
                    if (window.dispatchEvent) {
                        window.dispatchEvent(new CustomEvent('toast-notify', { detail: { message: d.message } }));
                    }
                }
            })
            .catch(err => {
                // Revert on error
                this.votedMap[requestId] = wasVoted;
                if (currentVotes !== null) {
                    this.votesMap[requestId] = currentVotes;
                }
            });
        }
    };
}
</script>
@endsection
