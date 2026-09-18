@extends('layouts.admin')

@section('title', 'Gestión de 20 Consolas — Administración ROMHUB')

@section('content')
<div class="space-y-6" x-data="{ openModal: false, editMode: false, currentConsole: {} }">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight font-sans">Gestión de 20 Ecosistemas & Consolas</h1>
            <p class="text-xs text-gray-400 font-mono mt-0.5">Control de arquitecturas soportadas, fabricantes y generación</p>
        </div>
        <button @click="editMode = false; currentConsole = { name: '', slug: '', manufacturer: 'Sony', generation: '6th Gen', release_year: 2000, order: 0 }; openModal = true" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs uppercase tracking-wide flex items-center gap-2 transition-all shadow-lg shadow-blue-600/30">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Añadir Consola
        </button>
    </div>

    <!-- 20 Consoles Grid Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($consoles as $console)
        <div class="bg-[#11141A] border border-[#232936] hover:border-blue-500/50 rounded-2xl p-5 flex flex-col justify-between space-y-4 group">
            
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded bg-[#0A0C0F] text-blue-400 border border-[#232936] text-[10px] font-mono font-bold">
                        {{ $console->manufacturer }}
                    </span>
                    <span class="text-[10px] font-mono text-gray-500">{{ $console->generation }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#0A0C0F] border border-[#232936] flex items-center justify-center text-blue-400 shrink-0">
                        <i data-lucide="disc" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-white truncate font-sans">{{ $console->name }}</h2>
                        <p class="text-[10px] font-mono text-gray-400">Año: {{ $console->release_year ?: 'Retro' }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs font-mono pt-1">
                    <span class="text-emerald-400 font-bold">{{ $console->games_count }} ROMs</span>
                    <span class="text-gray-500">Orden: #{{ $console->order }}</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-3 border-t border-[#232936] flex items-center justify-between font-mono text-xs">
                <a href="{{ route('consoles.show', $console->slug) }}" target="_blank" class="text-gray-400 hover:text-blue-400 flex items-center gap-1">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Ver Catálogo
                </a>

                <div class="flex items-center gap-1">
                    <button @click="editMode = true; currentConsole = {{ json_encode($console) }}; openModal = true" class="p-1.5 rounded bg-[#0A0C0F] hover:bg-[#171B22] text-gray-300 hover:text-white border border-[#232936]">
                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                    </button>
                    
                    <form action="{{ route('admin.consoles.destroy', $console->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta consola?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded bg-rose-500/10 hover:bg-rose-600 text-rose-400 hover:text-white border border-rose-500/20">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                </div>
            </div>

        </div>
        @endforeach
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl" @click.outside="openModal = false">
            <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                <h3 class="text-base font-bold text-white font-sans" x-text="editMode ? 'Editar Consola' : 'Nueva Consola'"></h3>
                <button @click="openModal = false" class="text-gray-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form :action="editMode ? '{{ url('admin/consoles') }}/' + currentConsole.id : '{{ route('admin.consoles.store') }}'" method="POST" class="space-y-4 font-sans text-xs">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-gray-400 mb-1 font-mono">Nombre de la Consola *</label>
                    <input type="text" name="name" x-model="currentConsole.name" required placeholder="Ej. PlayStation 2" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Fabricante</label>
                        <select name="manufacturer" x-model="currentConsole.manufacturer" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                            <option value="Sony">Sony</option>
                            <option value="Nintendo">Nintendo</option>
                            <option value="Microsoft">Microsoft</option>
                            <option value="Sega">Sega</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Generación</label>
                        <input type="text" name="generation" x-model="currentConsole.generation" placeholder="6th Gen" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Año de Lanzamiento</label>
                        <input type="number" name="release_year" x-model="currentConsole.release_year" placeholder="2000" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                    </div>
                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Orden de Clasificación</label>
                        <input type="number" name="order" x-model="currentConsole.order" placeholder="1" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-400 mb-1 font-mono">Descripción Breve</label>
                    <textarea name="description" x-model="currentConsole.description" rows="2" placeholder="Información histórica..." class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-[#232936]">
                    <button type="button" @click="openModal = false" class="px-4 py-2 rounded-lg bg-[#171B22] text-gray-300">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white font-bold">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
