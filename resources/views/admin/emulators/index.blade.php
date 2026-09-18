@extends('layouts.admin')

@section('title', 'Emuladores Recomendados — Panel de Administración')

@section('content')
<div class="space-y-6" x-data="{ 
    openModal: false, 
    editMode: false, 
    currentEmu: {
        id: null,
        name: '',
        system: '',
        icon: 'cpu',
        platforms: ['Windows'],
        version: '',
        features: '',
        license: 'GPLv3 Open Source',
        website: '',
        download_url: '',
        description: '',
        is_active: true,
        order: 0
    } 
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight font-sans flex items-center gap-2.5">
                <i data-lucide="cpu" class="w-6 h-6 text-emerald-400"></i> Emuladores Oficiales & Recomendados
            </h1>
            <p class="text-xs text-gray-400 font-mono mt-0.5">Gestiona enlaces oficiales de descarga, versiones y sistemas operativos soportados</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('emulators') }}" target="_blank" class="px-3 py-2 rounded-xl bg-[#11141A] hover:bg-[#171B22] border border-[#232936] text-gray-300 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i data-lucide="external-link" class="w-4 h-4 text-gray-400"></i> Ver en Web Pública
            </a>
            <button @click="editMode = false; currentEmu = { id: null, name: '', system: '', icon: 'cpu', platforms: ['Windows'], version: '', features: '', license: 'GPLv3 Open Source', website: '', download_url: '', description: '', is_active: true, order: {{ count($emulators) + 1 }} }; openModal = true" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs uppercase tracking-wide flex items-center gap-2 transition-all shadow-lg shadow-blue-600/30">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Nuevo Emulador
            </button>
        </div>
    </div>

    <!-- Emulators Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($emulators as $emu)
        <div class="bg-[#11141A] border border-[#232936] hover:border-blue-500/40 rounded-2xl p-5 flex flex-col justify-between space-y-4 transition-all group">
            
            <div class="space-y-3">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center font-bold">
                            <i data-lucide="{{ $emu->icon ?: 'cpu' }}" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-white font-sans group-hover:text-emerald-400 transition-colors">{{ $emu->name }}</h2>
                            <span class="text-[11px] text-gray-400 font-mono">{{ $emu->system }}</span>
                        </div>
                    </div>

                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ $emu->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20' }}">
                        {{ $emu->is_active ? 'ACTIVO' : 'OCULTO' }}
                    </span>
                </div>

                @if($emu->version)
                    <div class="flex items-center gap-2 text-[10px] font-mono">
                        <span class="px-1.5 py-0.5 rounded bg-[#0A0C0F] border border-[#232936] text-gray-300 font-bold">{{ $emu->version }}</span>
                        @if($emu->license)
                            <span class="text-gray-500">• {{ $emu->license }}</span>
                        @endif
                    </div>
                @endif

                <p class="text-xs text-gray-400 font-sans line-clamp-2">{{ $emu->description }}</p>

                <!-- Platform tags -->
                <div class="flex flex-wrap gap-1.5 pt-1">
                    @foreach($emu->platforms ?? [] as $plat)
                        <span class="px-2 py-0.5 rounded bg-[#0A0C0F] border border-[#232936] text-[10px] font-mono text-gray-300">
                            {{ $plat }}
                        </span>
                    @endforeach
                </div>
            </div>

            <!-- Footer links & action buttons -->
            <div class="pt-3 border-t border-[#232936] flex items-center justify-between font-mono text-xs">
                <div class="flex items-center gap-2">
                    <a href="{{ $emu->download_url }}" target="_blank" class="text-emerald-400 hover:underline flex items-center gap-1">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i> Descarga
                    </a>
                    @if($emu->website)
                        <span class="text-gray-600">|</span>
                        <a href="{{ $emu->website }}" target="_blank" class="text-gray-400 hover:text-white flex items-center gap-1">
                            <i data-lucide="globe" class="w-3.5 h-3.5"></i> Web
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-1.5">
                    @php
                        $emuArray = $emu->toArray();
                        $emuArray['features'] = is_array($emu->features) ? implode(', ', $emu->features) : ($emu->features ?? '');
                    @endphp
                    <button @click="editMode = true; currentEmu = {{ json_encode($emuArray) }}; openModal = true" class="p-1.5 rounded bg-[#0A0C0F] hover:bg-blue-600 text-gray-300 hover:text-white border border-[#232936] transition-colors" title="Editar">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    </button>

                    <form action="{{ route('admin.emulators.destroy', $emu->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este emulador?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded bg-rose-500/10 hover:bg-rose-600 text-rose-400 hover:text-white border border-rose-500/20 transition-colors" title="Eliminar">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </form>
                </div>
            </div>

        </div>
        @empty
        <div class="col-span-full p-8 text-center text-gray-500 font-mono text-xs bg-[#11141A] rounded-2xl border border-[#232936]">
            No hay emuladores registrados. Haz clic en "Nuevo Emulador" para agregar uno.
        </div>
        @endforelse
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="openModal" x-cloak class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
        <div class="bg-[#11141A] border border-[#232936] rounded-2xl max-w-2xl w-full p-6 space-y-4 shadow-2xl my-8" @click.outside="openModal = false">
            
            <div class="flex items-center justify-between border-b border-[#232936] pb-3">
                <h3 class="text-base font-bold text-white font-sans flex items-center gap-2" x-text="editMode ? 'Editar Emulador' : 'Nuevo Emulador'"></h3>
                <button @click="openModal = false" class="text-gray-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>

            <form :action="editMode ? '{{ url('admin/emulators') }}/' + currentEmu.id : '{{ route('admin.emulators.store') }}'" method="POST" class="space-y-4 font-sans text-xs">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Nombre del Emulador *</label>
                        <input type="text" name="name" x-model="currentEmu.name" required placeholder="Ej. PCSX2" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Sistema / Consola que Emula *</label>
                        <input type="text" name="system" x-model="currentEmu.system" required placeholder="Ej. PlayStation 2 (PS2)" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Ícono Lucide</label>
                        <input type="text" name="icon" x-model="currentEmu.icon" placeholder="disc, box, cpu, gamepad" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Versión Actual</label>
                        <input type="text" name="version" x-model="currentEmu.version" placeholder="Ej. v2.0 Stable" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Licencia</label>
                        <input type="text" name="license" x-model="currentEmu.license" placeholder="Ej. GPLv3 Open Source" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                    </div>
                </div>

                <!-- Operating Systems Supported -->
                <div>
                    <label class="block text-gray-400 mb-2 font-mono">Plataformas / Sistemas Operativos Compatibles:</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        @foreach(['Windows', 'Android', 'macOS', 'Linux', 'Steam Deck', 'iOS', 'Switch', '3DS', 'Xbox'] as $os)
                            <label class="flex items-center gap-2 p-2 rounded-lg bg-[#0A0C0F] border border-[#232936] cursor-pointer hover:border-emerald-500/40">
                                <input type="checkbox" name="platforms[]" value="{{ $os }}" :checked="currentEmu.platforms && currentEmu.platforms.includes('{{ $os }}')" class="rounded bg-[#171B22] border-[#232936] text-emerald-600 focus:ring-0">
                                <span class="text-xs text-gray-300 font-mono">{{ $os }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">URL de Descarga Oficial *</label>
                        <input type="url" name="download_url" x-model="currentEmu.download_url" required placeholder="https://pcsx2.net/downloads" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-emerald-400 font-mono">
                    </div>

                    <div>
                        <label class="block text-gray-400 mb-1 font-mono">Sitio Web Oficial / GitHub (Opcional)</label>
                        <input type="url" name="website" x-model="currentEmu.website" placeholder="https://pcsx2.net" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-400 mb-1 font-mono">Características Principales (Separadas por coma)</label>
                    <input type="text" name="features" x-model="currentEmu.features" placeholder="Resolución 4K, Vulkan & DX12, Soporte Mandos, RetroAchievements" class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white">
                </div>

                <div>
                    <label class="block text-gray-400 mb-1 font-mono">Descripción</label>
                    <textarea name="description" x-model="currentEmu.description" rows="2" placeholder="Resumen del emulador, compatibilidad y rendimiento..." class="w-full bg-[#0A0C0F] border border-[#232936] rounded-lg p-2.5 text-white"></textarea>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="currentEmu.is_active" class="rounded bg-[#0A0C0F] border-[#232936] text-emerald-600 focus:ring-0 w-4 h-4">
                        <span class="text-gray-300 font-mono">Visible en la Web Pública</span>
                    </label>

                    <div class="flex items-center gap-2">
                        <label class="text-gray-400 font-mono">Orden:</label>
                        <input type="number" name="order" x-model="currentEmu.order" class="w-16 bg-[#0A0C0F] border border-[#232936] rounded-lg p-1.5 text-white font-mono text-center">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-[#232936]">
                    <button type="button" @click="openModal = false" class="px-4 py-2 rounded-lg bg-[#171B22] text-gray-300">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold">Guardar Emulador</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
