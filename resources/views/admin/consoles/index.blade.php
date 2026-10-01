@extends('layouts.admin')

@section('title', 'Gestión de Ecosistemas & Consolas — Administración ROMHUB')

@section('content')
<div class="space-y-6" x-data="{ 
    openModal: {{ request('action') === 'create' ? 'true' : 'false' }}, 
    editMode: false, 
    currentConsole: {
        id: null,
        name: '',
        short_name: '',
        slug: '',
        manufacturer: 'Sony',
        generation: 8,
        release_year: 2013,
        recommended_emulator: '',
        description: '',
        order: 0,
        is_featured: false
    },
    searchQuery: '',
    selectedManufacturer: 'ALL',
    matches(con) {
        const matchesSearch = !this.searchQuery || 
            con.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
            (con.short_name && con.short_name.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
            con.slug.toLowerCase().includes(this.searchQuery.toLowerCase());
        const matchesMfg = this.selectedManufacturer === 'ALL' || con.manufacturer === this.selectedManufacturer;
        return matchesSearch && matchesMfg;
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-[#11141A] border border-[#232936] rounded-2xl p-5 shadow-xl">
        <div class="space-y-1">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-blue-600/20 text-blue-400 border border-blue-500/30 flex items-center justify-center">
                    <i data-lucide="tv" class="w-4.5 h-4.5"></i>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight font-sans">
                    Ecosistemas / Consolas
                </h1>
                <span class="px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20 text-xs font-mono font-bold">
                    {{ $consoles->count() }} Registradas
                </span>
            </div>
            <p class="text-xs text-gray-400 font-mono">
                Agrega, edita y elimina las plataformas disponibles para catálogo, importación y scrapers.
            </p>
        </div>

        <button @click="editMode = false; currentConsole = { id: null, name: '', short_name: '', slug: '', manufacturer: 'Sony', generation: 8, release_year: (new Date()).getFullYear(), recommended_emulator: '', description: '', order: {{ $consoles->count() + 1 }}, is_featured: false }; openModal = true" 
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-all shadow-lg shadow-blue-600/30 cursor-pointer active:scale-95">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Añadir Consola</span>
        </button>
    </div>

    <!-- Search & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-[#0A0C0F] border border-[#232936] rounded-xl p-3">
        <!-- Search input -->
        <div class="relative flex-1">
            <i data-lucide="search" class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" 
                   x-model="searchQuery" 
                   placeholder="Buscar consola por nombre o acrónimo (ej. PlayStation 4, PS4, Switch, SG-1000)..." 
                   class="w-full bg-[#11141A] border border-[#232936] rounded-lg pl-9 pr-3 py-2 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 font-sans">
        </div>

        <!-- Manufacturer Filter Chips -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5 text-xs font-mono">
            <button type="button" @click="selectedManufacturer = 'ALL'" 
                    :class="selectedManufacturer === 'ALL' ? 'bg-blue-600 text-white font-bold' : 'bg-[#11141A] text-gray-400 hover:text-white border border-[#232936]'"
                    class="px-2.5 py-1.5 rounded-lg transition-colors cursor-pointer shrink-0">
                Todas
            </button>
            <button type="button" @click="selectedManufacturer = 'Sony'" 
                    :class="selectedManufacturer === 'Sony' ? 'bg-blue-600 text-white font-bold' : 'bg-[#11141A] text-gray-400 hover:text-white border border-[#232936]'"
                    class="px-2.5 py-1.5 rounded-lg transition-colors cursor-pointer shrink-0">
                Sony
            </button>
            <button type="button" @click="selectedManufacturer = 'Nintendo'" 
                    :class="selectedManufacturer === 'Nintendo' ? 'bg-blue-600 text-white font-bold' : 'bg-[#11141A] text-gray-400 hover:text-white border border-[#232936]'"
                    class="px-2.5 py-1.5 rounded-lg transition-colors cursor-pointer shrink-0">
                Nintendo
            </button>
            <button type="button" @click="selectedManufacturer = 'Sega'" 
                    :class="selectedManufacturer === 'Sega' ? 'bg-blue-600 text-white font-bold' : 'bg-[#11141A] text-gray-400 hover:text-white border border-[#232936]'"
                    class="px-2.5 py-1.5 rounded-lg transition-colors cursor-pointer shrink-0">
                Sega
            </button>
            <button type="button" @click="selectedManufacturer = 'Microsoft'" 
                    :class="selectedManufacturer === 'Microsoft' ? 'bg-blue-600 text-white font-bold' : 'bg-[#11141A] text-gray-400 hover:text-white border border-[#232936]'"
                    class="px-2.5 py-1.5 rounded-lg transition-colors cursor-pointer shrink-0">
                Microsoft
            </button>
            <button type="button" @click="selectedManufacturer = 'Other'" 
                    :class="selectedManufacturer === 'Other' ? 'bg-blue-600 text-white font-bold' : 'bg-[#11141A] text-gray-400 hover:text-white border border-[#232936]'"
                    class="px-2.5 py-1.5 rounded-lg transition-colors cursor-pointer shrink-0">
                Otras
            </button>
        </div>
    </div>

    <!-- Consoles Grid Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($consoles as $console)
        <div x-show="matches({{ json_encode($console) }})" 
             x-transition
             class="bg-[#11141A] border border-[#232936] hover:border-blue-500/50 rounded-2xl p-5 flex flex-col justify-between space-y-4 group transition-all">
            
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded bg-[#0A0C0F] text-blue-400 border border-[#232936] text-[10px] font-mono font-bold">
                        {{ $console->manufacturer }}
                    </span>
                    <span class="text-[10px] font-mono text-gray-400">
                        {{ $console->generation ? "Gen {$console->generation}" : 'Clásica' }}
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#0A0C0F] border border-[#232936] flex items-center justify-center text-blue-400 shrink-0 group-hover:scale-105 transition-transform">
                        <i data-lucide="disc" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-white truncate font-sans group-hover:text-blue-400 transition-colors">
                            {{ $console->name }}
                        </h2>
                        <div class="flex items-center gap-2 text-[10px] font-mono text-gray-400 mt-0.5">
                            @if($console->short_name)
                                <span class="text-amber-400 font-bold">[{{ $console->short_name }}]</span>
                            @endif
                            <span>Año: {{ $console->release_year ?: 'Retro' }}</span>
                        </div>
                    </div>
                </div>

                @if($console->recommended_emulator)
                    <div class="text-[11px] font-mono text-purple-300 bg-purple-950/30 border border-purple-500/20 px-2 py-1 rounded-lg truncate">
                        <span class="text-purple-400 font-bold">Emu:</span> {{ $console->recommended_emulator }}
                    </div>
                @endif

                <div class="flex items-center justify-between text-xs font-mono pt-1">
                    <span class="text-emerald-400 font-bold flex items-center gap-1">
                        <i data-lucide="gamepad-2" class="w-3.5 h-3.5"></i>
                        <span>{{ number_format($console->games_count) }} ROMs</span>
                    </span>
                    <span class="text-gray-500 text-[11px]">Orden #{{ $console->order }}</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-3 border-t border-[#232936] flex items-center justify-between font-mono text-xs">
                <a href="{{ route('consoles.show', $console->slug) }}" target="_blank" class="text-gray-400 hover:text-blue-400 flex items-center gap-1 transition-colors">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>Ver Catálogo</span>
                </a>

                <div class="flex items-center gap-1.5">
                    <!-- Edit Button -->
                    <button type="button"
                            @click="editMode = true; currentConsole = {{ json_encode($console) }}; openModal = true" 
                            title="Editar esta consola"
                            class="p-1.5 rounded-lg bg-[#0A0C0F] hover:bg-[#171B22] text-gray-300 hover:text-white border border-[#232936] cursor-pointer transition-colors">
                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                    </button>
                    
                    <!-- Delete Button Form -->
                    <form action="{{ route('admin.consoles.destroy', $console->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar la consola \'{{ $console->name }}\'? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                title="Eliminar consola"
                                class="p-1.5 rounded-lg bg-rose-500/10 hover:bg-rose-600 text-rose-400 hover:text-white border border-rose-500/20 cursor-pointer transition-colors">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                </div>
            </div>

        </div>
        @endforeach
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="openModal" 
         x-cloak 
         class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl" 
             @click.outside="openModal = false">
            
            <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-blue-600/20 text-blue-400 flex items-center justify-center">
                        <i data-lucide="tv" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-base font-bold text-white font-sans" x-text="editMode ? 'Editar Ecosistema / Consola' : 'Nuevo Ecosistema / Consola'"></h3>
                </div>
                <button @click="openModal = false" class="text-gray-400 hover:text-white p-1 rounded-lg hover:bg-[#171B22] cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="editMode ? '{{ url('admin/consoles') }}/' + currentConsole.id : '{{ route('admin.consoles.store') }}'" method="POST" class="space-y-4 font-sans text-xs">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <!-- Name & Short Name -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-gray-300 mb-1 font-mono font-bold">Nombre Completo *</label>
                        <input type="text" 
                               name="name" 
                               x-model="currentConsole.name" 
                               required 
                               placeholder="Ej. PlayStation 4" 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-medium focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-1 font-mono font-bold">Siglas / Sigla</label>
                        <input type="text" 
                               name="short_name" 
                               x-model="currentConsole.short_name" 
                               placeholder="Ej. PS4" 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-medium focus:border-blue-500">
                    </div>
                </div>

                <!-- Slug & Manufacturer -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-gray-300 mb-1 font-mono font-bold">Slug URL (Identificador) *</label>
                        <input type="text" 
                               name="slug" 
                               x-model="currentConsole.slug" 
                               placeholder="ej. playstation-4" 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono focus:border-blue-500">
                        <span class="text-[10px] text-gray-500 mt-0.5 block">Usado en URLs y scrapers.</span>
                    </div>

                    <div>
                        <label class="block text-gray-300 mb-1 font-mono font-bold">Fabricante *</label>
                        <select name="manufacturer" 
                                x-model="currentConsole.manufacturer" 
                                class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono focus:border-blue-500 cursor-pointer">
                            <option value="Sony">Sony</option>
                            <option value="Nintendo">Nintendo</option>
                            <option value="Microsoft">Microsoft</option>
                            <option value="Sega">Sega</option>
                            <option value="Arcade">Arcade</option>
                            <option value="Other">Otro Fabricante</option>
                        </select>
                    </div>
                </div>

                <!-- Generation, Release Year & Order -->
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-gray-300 mb-1 font-mono font-bold">Generación (Nº)</label>
                        <input type="number" 
                               name="generation" 
                               x-model="currentConsole.generation" 
                               placeholder="Ej. 8" 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-1 font-mono font-bold">Año Lanzamiento</label>
                        <input type="number" 
                               name="release_year" 
                               x-model="currentConsole.release_year" 
                               placeholder="Ej. 2013" 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-gray-300 mb-1 font-mono font-bold">Orden Portada</label>
                        <input type="number" 
                               name="order" 
                               x-model="currentConsole.order" 
                               placeholder="Ej. 21" 
                               class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono focus:border-blue-500">
                    </div>
                </div>

                <!-- Recommended Emulator -->
                <div>
                    <label class="block text-gray-300 mb-1 font-mono font-bold">Emulador Recomendado</label>
                    <input type="text" 
                           name="recommended_emulator" 
                           x-model="currentConsole.recommended_emulator" 
                           placeholder="Ej. shadPS4 / fpPS4 (PC, Android)" 
                           class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white focus:border-blue-500">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-gray-300 mb-1 font-mono font-bold">Descripción Corta</label>
                    <textarea name="description" 
                              x-model="currentConsole.description" 
                              rows="2" 
                              placeholder="Breve reseña histórica o características..." 
                              class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white focus:border-blue-500"></textarea>
                </div>

                <!-- Featured Toggle -->
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" 
                           name="is_featured" 
                           id="isFeaturedConsole" 
                           value="1" 
                           x-model="currentConsole.is_featured" 
                           class="w-4 h-4 rounded text-blue-600 bg-[#0A0C0F] border-[#232936] focus:ring-blue-500">
                    <label for="isFeaturedConsole" class="text-xs text-gray-300 font-mono font-bold cursor-pointer">
                        Destacar en portada y menú de consolas
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#232936]">
                    <button type="button" 
                            @click="openModal = false" 
                            class="px-4 py-2.5 rounded-xl bg-[#171B22] hover:bg-[#232936] text-gray-300 hover:text-white font-mono text-xs cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-blue-600/30 cursor-pointer active:scale-95">
                        <span x-text="editMode ? 'Guardar Cambios' : 'Registrar Consola'"></span>
                    </button>
                </div>

            </form>
        </div>

    </div>

</div>
@endsection
